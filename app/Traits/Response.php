<?php

namespace App\Traits;

trait Response{

    public function success($error, $message, $data, $status = 200){
       return response()->json([
        'error' => $error,
        'message' => $message,
        'data' => $data
       ], $status);
    }

    public function error($message, $data, $status = 400){
        return response()->json([
            'error' => $message,
            'message' => $data
           ], $status);
    }
}
