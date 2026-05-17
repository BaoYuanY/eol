<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{

    public function json(array $data = []): JsonResponse
    {
        return response()->json($data)->header('Content-Type', 'application/json;charset=utf-8');
    }

    public function success(array $data = null): JsonResponse
    {
        return $this->json(['code' => 200, 'msg' => '', 'data' => $data ?: null]);
    }

    public function error(int $code, string $msg = '', array $data = null): JsonResponse
    {
        return $this->json(['code' => $code, 'msg' => $msg, 'data' => $data ?: null]);
    }
}
