<?php

namespace App\Helpers;

class ApiFormatter
{
    // protected static $response = [
    //     "status" => NULL,
    //     "message" => NULL,
    //     "data" => NULL,
    // ];
    public static function sendResponse($status = NULL,
    $success = false, $message = NULL, $data = [])
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}