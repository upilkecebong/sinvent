<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use DB;

class BarangKeluarController extends Controller
{
    public function index(Request $request)
    {
        $rsetBarangKeluar = BarangKeluar::with('barang')->latest()->paginate(10);
        return view('barangkeluar.index', compact('rsetBarangKeluar'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }
    
    public function create()
    {
        $abarangkeluar = Barang::all();
        return view('barangkeluar.create',compact('abarangkeluar'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'tgl_keluar'   => 'required|date',
            'qty_keluar'   => 'required|numeric|min:1',
            'barang_id'    => 'required|exists:barang,id',
        ]);

        $tgl_keluar = $request->tgl_keluar;
        $barang_id = $request->barang_id;

        // Check if there's any BarangMasuk with a date later than tgl_keluar
        $existingBarangMasuk = BarangMasuk::where('barang_id', $barang_id)
            ->where('tgl_masuk', '>', $tgl_keluar)
            ->exists();
    
        if ($existingBarangMasuk) {
            return redirect()->back()->withInput()->withErrors(['tgl_keluar' => 'Tanggal keluar tidak boleh mendahului tanggal masuk!']);
        }
    
        $barang = Barang::find($barang_id);

        //Validasi jika jumlah qty_keluar lebih besar dari stok saat itu, muncul pesan eror
        if ($request->qty_keluar > $barang->stok) {
            return redirect()->back()->withInput()->withErrors(['qty_keluar' => 'Jumlah barang keluar melebihi stok!']);
        }

        elseif ($request->qty_keluar < 1) {
            return redirect()->back()->withInput()->withErrors(['qty_keluar' => 'Jumlah barang keluar tidak boleh 0!']);
        }

        //create post
        BarangKeluar::create([
            'tgl_keluar'        => $request->tgl_keluar,
            'qty_keluar'        => $request->qty_keluar,
            'barang_id'        => $request->barang_id,
        ]);

        return redirect()->route('barangkeluar.index')->with(['success' => 'Data Barang Keluar Berhasil Disimpan!']);
    }

    public function destroy(string $id)
    {
        $rsetBarangKeluar = BarangKeluar::find($id);


        // Delete the main record in barang table
        $rsetBarangKeluar->delete();

        // Redirect to index
        return redirect()->route('barangkeluar.index')->with(['success' => 'Data Barang Keluar Berhasil Dihapus!']);
    }

    public function show(string $id)
    {
        $rsetBarangKeluar = BarangKeluar::find($id);

        //return view
        return view('barangkeluar.show', compact('rsetBarangKeluar'));
    }

    public function edit($id)
    {
        $rsetBarangKeluar = BarangKeluar::findOrFail($id);
        $abarangkeluar = Barang::all();
        $selectedBarang = Barang::find($rsetBarangKeluar->barang_id); 
        return view('barangkeluar.edit', compact('rsetBarangKeluar', 'abarangkeluar', 'selectedBarang'));    
    }

    public function update(Request $request, $id)
    {

        $request->validate( [
            'tgl_keluar'   => 'required|date',
            'qty_keluar'   => 'required|numeric|min:1',
            'barang_id'    => 'required|exists:barang,id',
        ]);

        $tgl_keluar = $request->tgl_keluar;
        $barang_id = $request->barang_id;

        // Check if there's any BarangMasuk with a date later than tgl_keluar
        $existingBarangMasuk = BarangMasuk::where('barang_id', $barang_id)
            ->where('tgl_masuk', '>', $tgl_keluar)
            ->exists();
    
        if ($existingBarangMasuk) {
            return redirect()->back()->withInput()->withErrors(['tgl_keluar' => 'Tanggal keluar tidak boleh mendahului tanggal masuk!']);
        }
    
        $barang = Barang::find($barang_id);


        //Validasi jika jumlah qty_keluar lebih besar dari stok saat itu, muncul pesan eror
        if ($request->qty_keluar > $barang->stok) {
            return redirect()->back()->withInput()->withErrors(['qty_keluar' => 'Jumlah barang keluar melebihi stok!']);
        }

        elseif ($request->qty_keluar < 1) {
            return redirect()->back()->withInput()->withErrors(['qty_keluar' => 'Jumlah barang keluar tidak boleh 0!']);
        }    

        //create post
        $rsetBarangKeluar = BarangKeluar::findOrFail($id);
            $rsetBarangKeluar->update([
                'tgl_keluar' => $request->tgl_keluar,
                'qty_keluar' => $request->qty_keluar,
                'barang_id' => $request->barang_id,
            ]);

        return redirect()->route('barangkeluar.index')->with(['success' => 'Data Berhasil Diupdate!']);
        }
}