<?php

namespace App\Http\Controllers;

use App\Models\Stuff;
use App\Models\StuffStock;
use App\Models\InboundStuff;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;



class InboundStuffController extends Controller
{
    public function index()
    {
        $iStuff = InboundStuff::with('stuff.stuffstock')->get();

        return ApiFormatter::sendResponse(200,true,'Lihat semua barang', $iStuff);
    // $stuff = InboundStuff::all();
    // $stuffstock = StuffStock::all();
    // $inboundstuff = InboundStuff::all();

    // if ($iStuff->isEmpty()) {
    //     return ApiFormatter::sendResponse(404,false,'Data tidak ditemukan', $iStuff);
    // }

    // return response()->json([
    //     'success' => true,
    //     'message' => 'Lihat semua stock barang',
    //     'data' => [
    //         'barang' => $stuff,
    //         'stock barang' => $stuffstock,
    //         'barang masuk' => $inboundstuff
    //     ]
    // ], 200);
    }

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stuff_id'   => 'required|integer',
            'total' => 'required',
            'date' => 'required',
            'proff_file' => 'required|file',
        ]);
            
        if ($validator->fails()) {
            return ApiFormatter::sendResponse(400, false, 'Semua Kolom Wajib Diisi!', $validator->errors());
        } else {
            $file = $request->file('proff_file');
            $fileName = $request->input('stuff_id') . '_' . strtotime($request->input('date')) . strtotime(date('H:i')) . '.' . $file->getClientOriginalExtension();
            $file->move('uploads', $fileName); //4
    
            $inbound = InboundStuff::create([
                'stuff_id'     => $request->input('stuff_id'),
                'total'   => $request->input('total'),
                'date'   => $request->input('date'),
                'proff_file'   => $fileName,
            ]);
    
            $stock = StuffStock::where('stuff_id', $request->input('stuff_id'))->first();
    
            if ($stock) {
                $total_stock = (int)$stock->total_available + (int)$request->input('total');
                $stock->update([
                    'total_available' => (int)$total_stock
                ]);
    
                return ApiFormatter::sendResponse(201, true, 'Barang Masuk Berhasil Disimpan!');
            } else {
                return ApiFormatter::sendResponse(404, false, 'Stok tidak ditemukan untuk stuff_id yang ditemukan.');
            }
        }
        // try {
        //     $this->validate([
        //         'stuff_id' => 'required',
        //         'total' => 'required',
        //         'date' => 'required',
        //         'proff_file' => 'required',
        //     ]);

        //     if ($validator->fails()) {
        //         return ApiFormatter::sendResponse(400, false, 'Semua Kolom Wajib Diisi!', $validator->erros());
        //     } else {
        //         $file = $request->file('proff_file');
        //         $fileName = $request->input('stuff_id') . '_' . strtotime($request->input('date')) . strtotime(date('H:i')) . '.' . $file->getClientOriginalExtension();
        //         $file->move('proff', $fileName);
        //     }

        //     $inbound = InboundStuff::create([
        //         'stuff_id' => $request->input('stuff_id'),
        //         'total' => $request->input('total'),
        //         'date' => $request->input('date'),
        //         'proff_file' => $fileName,
        //     ]);

        //     $stock = StuffStock::where('stuff_id', $request->input('stuff_id'))->first();
        // }
    }

    public function show($id)
    {
        try {
            $inbound = InboundStuff::with('stuff', 'stuff.stock')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "Lihat Barang Masuk dengan id $id", $inbound);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan", $th->getMessage());
        }
    }


        public function update(Request $request, $id)
        {
            try {
                $inbound = InboundStuff::with('stuff', 'stuff.stock')->findOrFail($id);


                $stuff_id = ($request->stuff_id) ? $request->stuff_id : $inbound->stuff_id;
                $total = ($request->total) ? $request->total : $inbound->total;
                $date = ($request->date) ? $request->date : $inbound->date;


                if ($request->file('proff_file') !== NULL) {
                    $file = $request->file('proff_file');
                    $fileName = $stuff_id . '_' . strtotime($date) . strtotime(date('H:i')) . '.' . $file->getClientOriginalExtension();
                    $file->move('public/uploads', $fileName); //3
                } else {
                    $fileName = $inbound->proff_file;
                }
                $total_s = $total - $inbound->total;
                $total_stock = (int)$inbound->stuff->stock->total_available + $total_s;
                $inbound->stuff->stock->update([
                    'total_available' => (int)$total_stock
                ]);
                if ($inbound) {
                    $inbound->update([
                        'stuff_id' => $stuff_id,
                        'total' => $total,
                        'date' => $date,
                        'proff_file' => $fileName
                    ]);
                    return ApiFormatter::sendResponse(200, true, "Berhasil Ubah Data Barang Masuk dengan id $id", $inbound);
                } else {
                    return ApiFormatter::sendResponse(400, false, "Proses gagal!");
                }
            } catch (\Throwable $th) {
                return ApiFormatter::sendResponse(400, false, "Proses Gagal!", $th->getMessage());
            }
        }

        public function deleted()
        {
            try {
                $stuff = InboundStuff::onlyTrashed()->get();
                //jika tidak ada data yang dihapus
                if ($stuff->count() === 0) {
                    return ApiFormatter::sendResponse(200, true, "Tidak ada data yang dihapus");
                }
                //menampilkan data-data yang dihapus
                return ApiFormatter::sendResponse(200, true, "Lihat Data Barang yang dihapus", $stuff);
            } catch (\Throwable $th) {
                return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
            }
        }

        public function restore($id)
        {
            try {
                $stuff = InboundStuff::onlyTrashed()->where('id', $id);

                $stuff->restore();
                //jika tidak ada data yang dihapus
                // if ($stuff->count() === 0) {
                //     return ApiFormatter::sendResponse(200, true, "Tidak ada data yang dihapus");
                // }
                //mengembalikan data-data yang dihapus
                return ApiFormatter::sendResponse(200, true, "Berhasil Mengembalikan data yang telah dihapus!", ['id' => $id]);
            } catch (\Throwable $th) {
                return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
            }
        }

        public function restoreAll()
        {
            try{
                $stuffs = InboundStuff::onlyTrashed();

                $stuffs->restore();
        
                // if ($stuffs->count() === 0) {
                //     return ApiFormatter::sendResponse(200, true, "Tidak ada data yang dihapus");
                // }
        
                return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan barang yang telah dihapus");
            }
            catch(\Throwable $th)
            {
                return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi", $th->getMessage());
            }
        }

        public function permanentDelete($id)
        {
            try {
                $inbound = InboundStuff::onlyTrashed()->findOrFail($id);
        
                $filePath = app()->basePath('public/uploads/' . $inbound->proff_file); //2
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
        
                $stock = StuffStock::where('stuff_id', $inbound->stuff_id)->first();
        
                $available = $stock->total_available - $inbound->total;
                $defect = ($available < 0) ? $stock->total_defect + ($available * -1) : $stock->total_defect;
        
                $stock->update([
                    'total_available' => $available,
                    'total_defect' => $defect
                ]);
        
                $inbound->forceDelete();
        
                return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah dihapus!", ['id' => $id]);
            } catch (\Throwable $th) {
                return ApiFormatter::sendResponse(404, false, "Proses gagal! Silakan coba lagi!", $th->getMessage());
            }
        }
        
        public function permanentDeleteAll()
        {
            try {
                $inbounds = InboundStuff::onlyTrashed()->get();
        
                foreach ($inbounds as $inbound) {
                    $filePath = app()->basePath('public/uploads/' . $inbound->proff_file); //1
                    if (File::exists($filePath)) {
                        File::delete($filePath);
                    }
        
                    $stock = StuffStock::where('stuff_id', $inbound->stuff_id)->first();
        
                    if ($stock) {
                        $available = $stock->total_available - $inbound->total;
                        $defect = ($available < 0) ? $stock->total_defect + ($available * -1) : $stock->total_defect;
        
                        $stock->update([
                            'total_available' => $available,
                            'total_defect' => $defect
                        ]);
                    }
        
                    $inbound->forceDelete();
                }
        
                return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen semua data yang telah dihapus!");
            } catch (\Throwable $th) {
                return ApiFormatter::sendResponse(500, false, "Proses gagal! Silakan coba lagi.", $th->getMessage());
            }
        }
    public function destroy($id)
    {
        try{
            $stuff = InboundStuff::findOrFail($id);
            $stock=StuffStock::where('stuff_id', $stuff->stuff_id)->first();

            $available_min =$stock->total_available - $stuff->total;
            $available = ($available_min < 0) ? 0 : $available_min;
            $defec =($available_min < 0) ? $stock ->total_defec + ($available * 1) : $stock->total_defec;
            $stock->update([
                'total_available' => $available,
                'total_defec' => $defec
            ]);

            $stuff->delete();
    
            return ApiFormatter::sendResponse(200, true, "Berhasil menghapus data dengan id $id",['id' => $id]);

    
        }        
    catch(\Throwable $th)
    {
        return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi", $th->getMessage());
    }
    }
}
