<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Restoration;
use Illuminate\Http\Request;

class RestorationController extends Controller
{
    public function index()
    {
        $restoration = Restoration::with('user', 'lending')->get();

        return ApiFormatter::sendResponse(200, true, 'Lihat Semua Pengembalian Barang', $restoration);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'user_id' => 'required',
                'lending_id' => 'required',
                'date_time' => 'required',
                'total_good_stuff' => 'required',
                'total_defec_stuff' => 'required',
            ]);
            $restoration = Restoration::create([
                'user_id' => $request->input('user_id'),
                'lending_id' => $request->input('lending_id'),
                'date_time' => $request->input('date_time'),
                'total_good_stuff' => $request->input('total_good_stuff'),
                'total_defec_stuff' => $request->input('total_defec_stuff'),
            ]);
            return ApiFormatter::sendResponse(200, true, 'Barang pengembalian berhasil dikembalikan', $restoration);
        } catch (\Throwable $th) {
            if ($th->validator->errors()) {
                return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan!', $th->validator->errors());
            } else {
                return ApiFormatter::sendResponse(400, false, 'Terdapat Keslahan Input', $th->getMessage());
            }
        }
    }

    public function show($id)
    {
        try {
            $restoration = Restoration::with('user', 'lending')->findOrFail($id); 
            
            return ApiFormatter::sendResponse(200, true, "Lihat Barang dengan id $id", $restoration);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $restoration = Restoration::findOrFail($id);
            $user_id = ($request->user_id) ? $request->user_id : $restoration->user_id;
            $lending_id = ($request->lending_id) ? $request->lending_id : $restoration->lending_id;
            $date_time = ($request->date_time) ? $request->date_time: $restoration->date_time;
            $total_good_stuff = ($request->total_good_stuff) ? $request->total_good_stuff : $restoration->total_good_stuff;
            $total_defec_stuff = ($request->total_defec_stuff) ? $request->total_defec_stuff : $restoration->total_defec_stuff;
    
            $restoration->update([
                'user_id' => $user_id,
                'lending_id' => $lending_id,
                'date_time' => $date_time,
                'total_good_stuff' => $total_good_stuff,
                'total_defec_stuff' => $total_defec_stuff,
            ]);
    
            return Apiformatter::sendResponse(200, true, "Berhasil Ubah data dengan id $id", $restoration);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    } 

    public function destroy($id)
    {
        try {
            $restoration = Restoration::findOrFail($id);
    
            $restoration->delete();

            return Apiformatter::sendResponse(200, true, "Berhasil Hapus Data dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return Apiformatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $restoration = Restoration::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "Berhasil Hapus Data Barang", ['id' => $restoration]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $restoration = Restoration::findOrFail($id);

            $restoration->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil Mengembalikan data dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $restoration = Restoration::onlyTrashed()->get();

            $restoration->restore();

            return ApiFormatter::sendResponse(200, )
        }
    }
}
