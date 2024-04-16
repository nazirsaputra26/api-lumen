<?php

namespace App\Http\Controllers;

use App\Models\Stuff;
use App\Models\StuffStock;
use App\Helpers\ApiFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StuffStockController extends Controller
{
    public function index()
    {
        $stuffStock = StuffStock::with('stuff')->get();
        $stuff = Stuff::get();
        $stock = stuffStock::get();

        $data = ['barang' => $stuff, 'stock' => $stock];

        return ApiFormatter::sendResponse(200, true, 'Lihat semua stock', $stuffStock);
        // $stuffStock = StuffStock::all();
        // $stuff = Stuff::all();

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Lihat semua stok barang',
        //     'data' => [
        //         'barang' => $stuff,
        //         'stock barang' => $stuffStock
        //     ]
        // ]);
    }

    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'stuff_id' => 'required',
        //     'total_available' => 'required',
        //     'total_defec' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Semua Kolom Wajib Diisi!',
        //         'data' => $validator->errors()
        //     ], 400);
        // } else {
        //     $stock = StuffStock::updateOrCreate([
        //         'stuff_id' => $request->input('stuff_id')
        //     ], [
        //         'total_available' => $request->input('total_available'),
        //         'total_defec' => $request->input('total_defec'),
        //     ]);

        //     if ($stock) {
        //         return response()->json([
        //             'success' => true,
        //             'message' => 'Stock Barang berhasil Disimpan!',
        //             'data' => $stock
        //         ], 201);
        //     } else {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Stock Barang Gagal Disimpan!',
        //         ], 400);
        //     }
        // }
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total_available' => 'required',
                'total_defec' => 'required',
            ]);
            $stuffStock = StuffStock::create([
                'stuff_id' => $request->input('stuff_id'),
                'total_available' => $request->input('total_available'),
                'total_defec' => $request->input('total_defec'),

            ]);
            // if ($stuff)
            return ApiFormatter::sendResponse(201, true, 'Barang Berhasil Disimpan!', $stuffStock);
        } catch (\Throwable $th) {
            // throw $th;
            if ($th->validator->errors()) {
                return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan Input Silahkan Coba Lagi!', $th->validator->errors());
            } else {
                return ApiFormatter::sendResponse(400, false, 'Terdapat Kesalahan Input Silahkan Coba Lagi!', $th->getMessage());
            }
        }
    }

    public function show($id)
    {
        // try {
        //     $stock = StuffStock::with('stuff')->find($id);

        //     return response()->json([
        //         'success' => true,
        //         'message' => "Lihat Stock Barang dengan id $id",
        //         'data' => $stock
        //     ], 200);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => "Data dengan id $id tidak ditemukan"
        //     ], 404);
        // }

        try {
            $stuffStock = StuffStock::with('stuff')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "Lihat Barang dengan id $id", $stuffStock);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Data Dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stock = StuffStock::with('stuff')->find($id);

            $total_available = ($request->total_available) ? $request->total_available : $stock->total_available;
            $total_defec = ($request->total_defec) ? $request->total_defec : $stock->total_defec;

            if ($stock) {
                $stock->update([
                    'total_available' => $total_available,
                    'total_defec' => $total_defec,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil Ubah Data Stock dengan id $id",
                    'data' => $stock
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Proses Gagal!"
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => "Proses Gagal Data dengan id $id tidak ditemukan!"
            ], 404);
        }

        try {
            $stuffStock = StuffStock::findOrFail($id);
            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $stuffStock->stuff_id;
            $total_available = ($request->total_available) ? $request->total_available : $stuffStock->total_available;
            $total_defec = ($request->total_defec) ? $request->total_defec : $stuffStock->total_defec;

            $stuffStock->update([
                'stuff_id' => $stuff_id,
                'total_available' => $total_available,
                'total_defec' => $total_defec,
            ]);

            return ApiFormatter::sendResponse(200, true, "Berhasil Ubah Data dengan id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function destroy($id)
    {
        // try {
        //     $stuffStock = StuffStock::findOrFail($id);
            
        //     $stuffStock->delete();

        //     return response()->json([
        //         'success' => true,
        //         'message' => "Berhasil Hapus Data dengan id $id",
        //         'data' => [
        //             'id' => $id,
        //         ]
        //     ], 200);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => "Proses Gagal! Data dengan id $id tidak ditemukan",
        //     ], 404);
        // }

        try {
            $stuffStock = StuffStock::findOrFail($id);

            $stuffStock->delete();

            return ApiFormatter::sendResponse(200, true, "Berhasil Hapus Data Barang dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses Gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $stuffStock = StuffStock::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "Lihat Data Barang yang dihapus", $stuffStock);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $stuffStock = StuffStock::onlyTrashed()->findOrFail($id);
            $has_stock = StuffStock::where('stuff_id', $stuffStock->stuff_id)->get();

            if ($has_stock->count() == 1) {
                $message = "Data stock sudah ada, tidak ada duplikat data stock untuk satu barang silahkan update data
                stock dengan id stock $stuffStock->stuff_id";
            } else {
                $stuffStock->restore();
                $message = "Berhasil Mengembalikan data yang telah di hapus!";
            }

            return ApiFormatter::sendResponse(200, true, $message, ['id' => $id, 'stuff_id' => $stuffStock->stuff_id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $stuffStock = StuffStock::onlyTrashed()->restore();

            $stuff_id = $stuffStock->pluck('stuff_id');

            $has_stock = $stuffStock::whereIn('stuff_id', $stuff_id)->get();


            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan semua data yang telah dihapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $stuffStock = StuffStock::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll() 
    {
        try {
            $stuffStock = StuffStock::onlyTrashed()->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen semua data yang ttelah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }
}
