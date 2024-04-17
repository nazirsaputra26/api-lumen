<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\models\InboundStuff;
use App\models\Stuff;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\models\StuffStock;
use Illuminate\Support\Facades\File;

class InboundStuffController extends Controller
{
    public function index(){
        // $inboundStuff = InboundStuff::all();

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Lihat semua barang',
        //     'data' => $inboundStuff
        // ],200);
        $inboundStock = InboundStuff::with('stuff','stuffStock')->get();

        return ApiFormatter::sendResponse(200, true, 'Lihat Semua stock Barang', $inboundStock);
    
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stuff_id' => 'required',
            'total' => 'required',
            'date' => 'required',
            'proff_file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust file validation as needed
        ]);

        if ($validator->fails()) {
            return ApiFormatter::sendResponse(400, false, 'semua kolom wajib diisi!', $validator->errors());
        } else {
            $file = $request->file('proff_file');
            $filename = $request->input('stuff_id') . '_' . strtotime($request->input('date')) . strtotime(date('H:i')) . '_' . $file->getClientOriginalExtension();
            $file->move(app()->basePath('public/uploads'), $filename);

            $inbound = InboundStuff::create([
                'stuff_id' => $request->input('stuff_id'),
                'total' => $request->input('total'),
                'date' => $request->input('date'),
                'proff_file' => $filename,
            ]);

            $stock = StuffStock::where('stuff_id', $request->input('stuff_id'))->first();

            $total_stock = (int)$stock->total_available + (int)$request->input('total');

            $stock->update([
                'total_available' => (int)$total_stock
            ]);

            if ($inbound && $stock) {
                return ApiFormatter::sendResponse(201, true, 'Barang Masuk Berhasil Dismpan!');
            } else {
                return ApiFormatter::sendResponse(404, false, 'Barang Masuk gagal didimpan!');
            }
        }
        
    }
    public function show($id){
        try {
            $inbound = InboundStuff::with('stuff', 'stuff.stock')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "Lihat Barang Masuk dengan id $id", $inbound);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan", $th->getMessage());
        }
    }

    public function update(Request $request, $id){
        try{
            // with('stuff', 'stuff.stock')->
            $inbound = inboundStuff::with('stuff', 'stuff.stock')->findOrFail($id);
            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $inbound->stuff_id;
            $total = ($request->total)? $request->total : $inbound->total;
            $date = ($request->date)? $request->date : $inbound->date;
            // $proff_file = ($request->proff_file)? $request->proff_file : $inboundStuff->proff_file;

            if ($request->file('proff_file') !== NULL) {
                $file = $request->file('proff_file');
                $filename = $stuff_id . '_' . strtotime($date) . strtotime(date('H:i')) . '.' .
                $file->getClientOriginalExtension();
                $file->move('proff', $filename);
            } else {
                $filename = $inbound->proff_file;
            }

            $total_s = $total - $inbound->total;

            $total_stock = (int)$inbound->stuff->stock->total_available + $total_s;

            $inbound->stuff->stock->update([
                'total_available' => (int)$total_stock,
            ]);

            if ($inbound) {
                $inbound->update([
                    'stuff_id' => $stuff_id,
                    'total' => $total,
                    'date' => $date,
                    'proff_file' => $filename
                ]);
                return ApiFormatter::sendResponse(200, true, "Berhasil ubah data barang masuk dengan id $id", $inbound);
            } else {
                return ApiFormatter::sendResponse(400, false, "Proses gagal!");
            }
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(400, false, "Proses gagal!", $th->getMessage());
        }
    }

    public function destroy($id){
        try{
            $inbound =  inboundStuff::findOrFail($id);
            $stock = StuffStock::where('stuff_id', $inbound->stuff_id)->first();
    
            $available_min = $stock->total_available - $inbound->total;
            $available = ($available_min < 0) ? 0 : $available_min;
            $defec = ($available_min < 0) ? $stock->total_defect + ($available * -1) : $stock->total_defec;
            $stock->update([
                'total_available' => $available,
                'total_defect' => $defec,
            ]);
            $inbound->delete();
    
            return ApiFormatter::sendResponse(200, true, "Berhasil Hapus data dengen id $id", ['id' => $id]);
        } catch(\Throwable $th){
            return ApiFormatter::sendResponse(400, false, "Proses gagal!", $th->getMessage());
        }
    }
    
    public function deleted()
    {
        try {

            $inbound = InboundStuff::onlyTrashed()->get();
    
            return ApiFormatter::sendResponse(200, true, "Lihat Data Pengemblian yang dihapus", $inbound);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    } 

    public function restore($id)
    {
        try {
            $inbound = InboundStuff::onlyTrashed()->where('id', $id);

            $stock = StuffStock::where('stuff_id', $inbound->stuff_id)->first();

            $available = $stock->total_available + $inbound->total;
            $available_min = $inbound->total - $stock->total_available;
            $defect = ($available_min < 0) ? $stock->total_defect + ($available_min * -1) : $stock->total_defect;

            $stock->update([
                'total_available' => $available,
                'total_defect' => $defect
            ]);

            $inbound->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $inbound = InboundStuff::onlyTrashed();

            foreach ($inbound->get() as $inbound) {
                $stock = StuffStock::where('stuff_id', $inbound->stuff_id)->first();

                $available = $stock->total_available + $inbound->total;
                $available_min = $inbound->total - $stock->total_available;
                $defect = ($available_min < 0) ? $stock->total_defect + ($available_min * -1) : $stock->total_defect;

                $stock->update([
                    'total_available' => $available,
                    'total_defect' => $defect
                ]);
            }

            $inbound->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan semua data yang telah dihapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    // public function permanentDelete($id)
    // {
    //     try {
    //         $inbound = InboundStuff::onlyTrashed()->when('id', $id)->forceDelete();

    //         $inbound->delete();

    //         return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanent semua data yang telah dihapus");
    //     } catch (\Throwable $th) {
    //         return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
    //     }
    // }

    // public function permanentDeleteAll()
    // {
    //     try {
    //         $inbound = InboundStuff::onlyTrashed();
    
    //         $inbound->delete();

    //         return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanent semua data yang telah dihapus");
    //     } catch (\Throwable $th) {
    //         return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
    //     }
    // } 
    
    public function permanentDelete($id)
    {
        try {
            $inbound = InboundStuff::onlyTrashed()->findOrFail($id);
    
            $filePath = app()->basePath('public/proff/' . $inbound->proff_file);
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
                $filePath = app()->basePath('public/proff/' . $inbound->proff_file);
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
}