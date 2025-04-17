<?php

namespace App\Api\Responses\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ApiResponds
{
    public function success($data = [], string $message = 'Operazione completata', array $extra = []): JsonResponse
    {
        $data = $this->normalizeData($data);

        return response()->json(array_merge([
            'status' => 'success',
            'message' => $message,
        ], $extra, [
            'data' => $data
        ]), 200);
    }

    public function warning($data = [], string $message = 'Operazione completata con avvisi', array $warnings = [], array $extra = []): JsonResponse
    {
        $data = $this->normalizeData($data);

        Log::warning($message, ['warnings' => $warnings]);

        return response()->json(array_merge([
            'status' => 'warning',
            'message' => $message,
            'warnings' => $warnings,
        ], $extra, [
            'data' => $data
        ]), 200);
    }

    public function error(string $message = 'Errore imprevisto', int $status = 500, array $errors = []): JsonResponse
    {

        Log::error($message, ['errors' => $errors]);

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'data' => [],
        ], $status);
    }

    protected static function normalizeData($data)
    {
        if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource) {
            return $data->resolve();
        }
    
        if ($data instanceof \Illuminate\Contracts\Support\Arrayable) {
            return $data->toArray(request());
        }
    
        return $data;
    }
}
