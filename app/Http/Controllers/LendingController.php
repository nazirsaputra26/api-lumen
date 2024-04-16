<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Stuff;
use App\Models\Restoration;
use App\Models\Lending;


class LendingController extends Controller
{
    public function index()
    {
        $lending = Lending::with('stuff', 'restoration',)->get();

        return ApiFormatter::sendResponse(200, true, 'Lihat Semua peminjaman', $lending);
    }

    // stuff_id", "date_time", "name", "user_id", "notes", "total_stuff

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'user_id' => 'required',
                'notes' => 'required',
                'total_stuff' => 'required',
            ]);
            $lending = Lending::create([
                'stuff_id' => $request->input('stuff_id'),
                'date_time' => $request->input('date_time'),
                'name' => $request->input('name'),
                'user_id' => $request->input('user_id'),
                'notes' => $request->input('notes'),
                'total_stuff' => $request->input('total_stuff'),
            ]);
            return ApiFormatter::sendResponse(200, true, 'Barang Berhasil Dipinjam!', $lending);
        } catch (\Throwable $th) {
            if ($th->validator->errors()) {
                return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan Input Silahkan Coba Lagi!', $th->validator->errors());
            } else {
                return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan Input Silahkan Coba Lagi!', $th->getMessage());
            }
        }
    }

    public function show($id)
    {
        try {
            $lending = Lending::with('stuff', 'restorations')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "Lihat Barang Peminjaman dengan id $id", $lending);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lending = Lending::findOrFail($id);
            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $lending->stuff_id;
            $date_time = ($request->date_time) ? $request->date_time : $lending->date_time;
            $name = ($request->name) ? $request->name : $lending->name;
            $user_id = ($request->user_id) ? $request->user_id : $lending->user_id;
            $notes = ($request->notes) ? $request->notes : $lending->notes;
            $total_stuff = ($request->total_stuff) ? $request->total_stuff : $lending->total_stuff;

            $lending->update([
                'stuff_id' => $stuff_id,
                'date_time' => $date_time,
                'name' => $name,
                'user_id' => $user_id,
                'notes' => $notes,
                'total_stuff' => $total_stuff,
            ]);
            
            return ApiFormatter::sendResponse(200, true, "Berhasil Ubah Data peminjaman dengan id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses Gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $lending = Lending::findOrFail($id);

            $lending->delete();

            return ApiFormatter::sendResponse(200, true, "Berhasil Hapus Data Peminjaman Barang dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses Gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $lending = Lending::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "Lihat Data Peminjamana Barang yang digapus", $lending);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses Gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $lending = Lending::onlyTrashed()->where('id', $id);

            $lending->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil Mengembalikan data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $lending = Lending::onlyTrashed();
    
            $lending->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan semua data yang telah dihapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    } 

    public function permanentDelete($id) 
    {
        try {
            $lending = Lending::onlyTrashed()->when('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanent data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi", $th->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $lending = Lending::onlyTrashed();

            $lending->delete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanent semua data yang telah dihapus");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }
}
