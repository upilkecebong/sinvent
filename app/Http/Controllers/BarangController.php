<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Barang;

use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index(Request $request)
    {

        $keyword = $request->input('keyword');

        $rsetBarang = Barang::where('merk', 'LIKE', "%$keyword%")
        ->orWhere('seri', 'LIKE', "%$keyword%")
        ->orWhere('spesifikasi', 'LIKE', "%$keyword%")
        ->orWhere('stok', 'LIKE', "%$keyword%")
        ->orWhereHas('kategori', function ($query) use ($keyword) {
            $query->where('deskripsi', 'LIKE', "%$keyword%");
            })
            ->paginate(10);
            
        return view('barang.index', compact('rsetBarang'))->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        $akategori = Kategori::all();
        return view('barang.create',compact('akategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'merk'          => 'required',
            'seri'          => 'required',
            'spesifikasi'   => 'required',
            'stok'          => 'required',
            'kategori_id'   => 'required',

        ]);

        Barang::create([
            'merk'             => $request->merk,
            'seri'             => $request->seri,
            'spesifikasi'      => $request->spesifikasi,
            'stok'             => $request->stok,
            'kategori_id'      => $request->kategori_id,
        ]);

        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function show(string $id)
    {
        $rsetBarang = Barang::find($id);

        return view('barang.show', compact('rsetBarang'));
    }

    public function edit(string $id)
    {
    $akategori = Kategori::all();
    $rsetBarang = Barang::find($id);
    $selectedKategori = Kategori::find($rsetBarang->kategori_id);

    return view('barang.edit', compact('rsetBarang', 'akategori', 'selectedKategori'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'merk'        => 'required',
            'seri'        => 'required',
            'spesifikasi' => 'required',
            'stok'        => 'required',
            'kategori_id' => 'required',
        ]);

        $rsetBarang = Barang::find($id);

            $rsetBarang->update([
                'merk'          => $request->merk,
                'seri'          => $request->seri,
                'spesifikasi'   => $request->spesifikasi,
                'stok'          => $request->stok,
                'kategori_id'   => $request->kategori_id,
            ]);

        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Diubah!']);
    }


    public function destroy(string $id)
    {
        $rsetBarang = Barang::find($id);

        // Check if stok is greater than 0 before deleting
        if ($rsetBarang->stok > 0) {
            return redirect()->route('barang.index')->with(['error' => 'Barang dengan stok lebih dari 0 tidak dapat dihapus!']);
        }
        // Delete post
        $rsetBarang->delete();

        // Redirect to index
        return redirect()->route('barang.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}