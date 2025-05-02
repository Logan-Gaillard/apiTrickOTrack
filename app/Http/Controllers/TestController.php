<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController
{
    public function responseGet()
    {
        return response()->json([
            'message' => 'Hello, World!'
        ]);
    }

    public function responsePost(Request $request)
    {
        // Log the Bearer token from the Authorization header
        $bearerToken = $request->bearerToken();
        Log::channel('stderr')->info('Bearer Token:', ['token' => $bearerToken]);

        // Log the request data
        Log::channel('stderr')->info('Request Data:', $request->all());

        return response()->json([
            'message' => 'Data received successfully!',
            'token' => $bearerToken,
            'data' => $request->all()
        ]);
    }
}
