<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponser
{
    /**
     * Success response
     */
    protected function successResponse($data, $message = null, $code = 200): JsonResponse
    {
        // Verifica se $data é uma coleção paginada
        if ($data instanceof ResourceCollection && $data->resource instanceof \Illuminate\Pagination\AbstractPaginator) {
            $paginationData = $data->resource->toArray();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $paginationData['data'],
                'pagination' => [
                    'total' => $paginationData['total'],
                    'per_page' => $paginationData['per_page'],
                    'current_page' => $paginationData['current_page'],
                    'last_page' => $paginationData['last_page'],
                    'from' => $paginationData['from'],
                    'to' => $paginationData['to'],
                    'path' => $paginationData['path'] ?? null,
                    'first_page_url' => $paginationData['first_page_url'] ?? null,
                    'last_page_url' => $paginationData['last_page_url'] ?? null,
                    'next_page_url' => $paginationData['next_page_url'],
                    'prev_page_url' => $paginationData['prev_page_url'],
                ]
            ], $code);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error response
     */
    protected function errorResponse($message = null, $errors = [], $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
