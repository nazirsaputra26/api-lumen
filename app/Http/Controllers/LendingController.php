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
use App\Models\StuffStock;

class LendingController extends Controller
{
    public function index()
    {
        try {
            $data = Lending::with('stuff', 'user', 'restoration')->get();

            return ApiFormatter::sendResponse(200, true, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, 'bad request', $err->getMessage());
        }
    }

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // stuff_id", "date_time", "name", "user_id", "notes", "total_stuff

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                // 'user_id' => 'required',
                // 'notes' => 'required',
                'total_stuff' => 'required',
            ]);

            $totalAvailable = StuffStock::where('stuff_id', $request->stuff_id)->value('total_available');

            if (is_null($totalAvailable)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Belum ada data inbound!');
            } elseif ((int)$request->total_stuff > (int)$totalAvailable) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Stok tidak tersedian!');
            } else {
                $lending = Lending::create([
                    'stuff_id' => $request->stuff_id,
                    'date_time' => $request->date_time,
                    'name' => $request->name,
                    'notes' => $request->notes ? $request->notes : '-',
                    'total_stuff' => $request->total_stuff,
                    'user_id' => auth()->user()->id,
                ]);

                $totalAvailableNow = (int)$totalAvailable - (int)$request->total_stuff;
                $stuffStock = StuffStock::where('stuff_id', $request->stuff_id)->update(['total_available' => $totalAvailableNow]);

                $dataLending = Lending::where('id', $lending['id'])->with('user', 'stuff', 'stuff.stuffStock')->first();

                return ApiFormatter::sendResponse(200, 'success', $dataLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function show($id)
    {
        // try {
        //     $lending = Lending::with('stuff', 'restorations')->findOrFail($id);

        //     return ApiFormatter::sendResponse(200, true, "Lihat Barang Peminjaman dengan id $id", $lending);
        // } catch (\Throwable $th) {
        //     return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan");
        // }

        try {
            $data = Lending::where('id', $id)->with('user', 'restoration', 'restoration.user', 'stuff', 'stuff.stuffStock')->first();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
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
            // Periksa apakah peminjaman memiliki restorasi
            $hasRestoration = Lending::where('id', $id)->whereHas('restoration')->exists();

            if ($hasRestoration) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Peminjaman sudah memiliki pengembalian, tidak dapat dibatalkan.');
            }

            // Ambil data peminjaman sebelum dihapus
            $lending = Lending::find($id);
            $totalStuff = $lending->total_stuff;
            $stuffId = $lending->stuff_id;

            // Hapus peminjaman
            //$checkproses = $lending->delete();

            // if ($checkproses) {
                // Kembalikan total_stuff ke total_available pada stuff_stock
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
                if ($stuffStock) {
                    $stuffStock->total_avaliable += $totalStuff;
                    $stuffStock->save();
                    $checkproses = $lending->delete();
                }

                return ApiFormatter::sendResponse(200, 'success', 'Berhasil hapus data Peminjaman.');
            // }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
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
            $lending = Lending::onlyTrashed()->where('id', $id)->forceDelete();
            

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
