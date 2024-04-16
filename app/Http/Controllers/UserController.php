<?php

namespace App\Http\Controllers;

use App\Models\Lending;
use App\Models\Restoration;
use App\Helpers\ApiFormatter;
use App\Models\User;
// use App\Http\Controllers\bcrypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $user = User::with('lendings','restorations')->get();

        return ApiFormatter::sendResponse(200, true, 'Lihat semua barang', $user);

        // return response()->json([
        //     'success' => true,
        //     'message' => 'Lihat semua barang',
        //     'data' => $stuff
        // ], 200);
    }

    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required',
        //     'category' => 'required',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Semua kolom wajib Diisi!',
        //         'data' => $validator->errors()
        //     ], 400);
        // } else {
        //     $stuff = Stuff::create([
        //         'name' => $request->input('name'),
        //         'category' => $request->input('category'),
        //     ]);

        //     if ($stuff) {
        //         return response()->json([
        //             'success' => true,
        //             'message' => 'Barang Berhasil Disimpan!',
        //             'data' => $stuff
        //         ], 201);
        //     } else {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Barang Gagal Disimpan!',
        //         ], 400);
        //     }
        // } 'username', 'email', 'password', 'role'

        try {
            $this->validate($request, [
                'username' => 'required|unique:users|min:3',
                'email' => 'required|unique:users|email',
                'password' => 'required|min:6',
                'role' => ['required', Rule::in(['admin', 'staff'])]
            ]);
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                // 'password' => Hash($request->input('password')),
                'password' => $request->has('password'),
                'role' => $request->input('role'),
                // $user->password = bcrypt($request->input('password'));
            ]);
            // if ($stuff)
            return ApiFormatter::sendResponse(201, true, 'Barang Berhasil Disimpan!', $user);
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
        //     $stuff = Stuff::findOrFail($id);

        //     return response()->json([
        //         'success' => true,
        //         'message' => "Lihat Barang dengan id $id",
        //         'data' => $stuff
        //     ], 200);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => "Data dengan id $id tidak ditemukan",
        //     ], 400);
        // }

        try {
            $user = User::with('lendings','restorations')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "Lihat Barang dengan id $id", $user);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Data Dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        // try {
        //     $stuff = Stuff::findOrFail($id);

        //     $name = ($request->name) ? $request->name : $stuff->name;
        //     $category = ($request->category) ? $request->category : $stuff->category;

        //     if ($stuff) {
        //         $stuff->update([
        //             'name' => $name,
        //             'category' => $category,
        //         ]);

        //         return response()->json([
        //             'success' => true,
        //             'message' => "Berhasil Ubah Data dengan id $id",
        //             'data' => $stuff
        //         ], 200);
        //     } else {
        //         return response()->json([
        //             'success' => false,
        //             'message' => "Proses gagal!"
        //         ], 404);
        //     }
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => "Proses gagal! Data dengan id $id tidak ditemukan",
        //     ], 404);
        // }

        try {
            $user = User::findOrFail($id);
            $username = ($request->username) ? $request->username : $user->username;
            $email = ($request->email) ? $request->email : $user->email;
            $password = ($request->password) ? $request->password : $user->password;
            $role = ($request->role) ? $request->role : $user->role;

            $user->update([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role,
            ]);

            return ApiFormatter::sendResponse(200, true, "Berhasil Ubah Data dengan id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function destroy($id)
    {
        // try {
        //     $stuff = Stuff::findOrFail($id);
            
        //     $stuff->delete();

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
            $user = User::findOrFail($id);

            $user->delete();

            return ApiFormatter::sendResponse(200, true, "Berhasil Hapus Data Barang dengan id $id", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses Gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $user = User::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "Lihat Data Barang yang dihapus", $user);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $user = User::onlyTrashed()->where('id', $id);

            $user->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil Mengembalikan data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $user = User::onlyTrashed();

            $user->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan semua data yang telah dihapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $user = User::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll() 
    {
        try {
            $user = User::onlyTrashed();

            $user->delete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen semua data yang telah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }
}
