<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use DB;

class BarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $rsetBarangMasuk = BarangMasuk::with('barang')->latest()->paginate(10);
        return view('barangmasuk.index', compact('rsetBarangMasuk'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }
    
    public function create()
    {
        $abarangmasuk = Barang::all();
        return view('barangmasuk.create',compact('abarangmasuk'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'tgl_masuk'     => 'required|date',
            'qty_masuk'     => 'required|numeric|min:1',
            'barang_id'     => 'required|exists:barang,id',
        ]);
    
        $tgl_masuk = $request->tgl_masuk;
        $barang_id = $request->barang_id;
    
        // Check if there's any BarangKeluar with a date earlier than tgl_masuk
        // $existingBarangKeluar = BarangKeluar::where('barang_id', $barang_id)
        //     ->where('tgl_keluar', '<', $tgl_masuk)
        //     ->exists();
    
        // if ($existingBarangKeluar) {
        //     return redirect()->back()->withInput()->withErrors(['tgl_masuk' => 'Tanggal masuk tidak boleh melebihi tanggal keluar!']);
        // }

        //Validasi jika jumlah qty_masuk berisi 0 maka muncul pesan eror
        if ($request->qty_masuk < 1) {
            return redirect()->back()->withInput()->withErrors(['qty_masuk' => 'Jumlah barang masuk tidak boleh 0!']);
        }

        //create post
        BarangMasuk::create([
            'tgl_masuk'        => $tgl_masuk,
            'qty_masuk'        => $request->qty_masuk,
            'barang_id'        => $barang_id,
        ]);
        return redirect()->route('barangmasuk.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function destroy(string $id)
    {
        $rsetBarangMasuk = BarangMasuk::find($id);


        // Delete the main record in barang table
        $rsetBarangMasuk->delete();

        // Redirect to index
        return redirect()->route('barangmasuk.index')->with(['success' => 'Data Barang Masuk Berhasil Dihapus!']);
    }

    public function show(string $id)
    {
        $rsetBarangMasuk = BarangMasuk::find($id);

        //return view
        return view('barangmasuk.show', compact('rsetBarangMasuk'));
    }

    public function edit(string $id)
    {
        $rsetBarangMasuk = BarangMasuk::findOrFail($id);
        $abarangmasuk = Barang::all();
        $selectedBarang = Barang::find($rsetBarangMasuk->barang_id); 
        return view('barangmasuk.edit', compact('rsetBarangMasuk', 'abarangmasuk', 'selectedBarang'));

    }

    public function update(Request $request, string $id)
    {

        $request->validate( [
            'tgl_masuk'     => 'required|date',
            'qty_masuk'     => 'required|numeric|min:1',
            'barang_id'     => 'required|exists:barang,id',
        ]);

        $tgl_masuk = $request->tgl_masuk;
        $barang_id = $request->barang_id;

        // Check if there's any BarangKeluar with a date earlier than tgl_masuk
        $existingBarangKeluar = BarangKeluar::where('barang_id', $barang_id)
            ->where('tgl_keluar', '<', $tgl_masuk)
            ->exists();
    
        if ($existingBarangKeluar) {
            return redirect()->back()->withInput()->withErrors(['tgl_masuk' => 'Tanggal masuk tidak boleh melebihi tanggal keluar!']);
        }

        //create post
        $rsetBarangMasuk = BarangMasuk::findOrFail($id);
            $rsetBarangMasuk->update([
                'tgl_masuk' => $request->tgl_masuk,
                'qty_masuk' => $request->qty_masuk,
                'barang_id' => $barang_id,
            ]);
        
        //Validasi jika jumlah qty_masuk berisi 0 atau min maka muncul pesan eror
        if ($request->qty_masuk < 1) {
            return redirect()->back()->withInput()->withErrors(['qty_masuk' => 'Jumlah barang masuk tidak boleh kurang dari 1!']);
        }

        return redirect()->route('barangmasuk.index')->with(['success' => 'Data Berhasil Diupdate!']);
    }


}