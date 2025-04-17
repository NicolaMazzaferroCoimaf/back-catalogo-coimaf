<?php

namespace App\Api\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ApiResponse
{
    public static function success($data = [], string $message = 'Operazione completata', array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'status' => 'success',
            'message' => $message,
        ], $extra, [
            'data' => $data
        ]), 200);
    }

    public static function warning($data = [], string $message = 'Operazione completata con avvisi', array $warnings = [], array $extra = []): JsonResponse
    {
        Log::warning($message, ['warnings' => $warnings]);

        return response()->json(array_merge([
            'status' => 'warning',
            'message' => $message,
            'warnings' => $warnings,
        ], $extra, [
            'data' => $data
        ]), 200);
    }

    public static function error(string $message = 'Errore imprevisto', int $status = 500, array $errors = [], $data = []): JsonResponse
    {
        Log::error($message, ['errors' => $errors]);

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ], $status);
    }
}
