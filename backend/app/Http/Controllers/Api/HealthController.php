<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class HealthController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}

