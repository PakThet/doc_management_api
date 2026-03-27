<?php
// app/Http/Controllers/Api/BaseController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /**
     * Success response method
     */
    protected function sendResponse($data, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Error response method
     */
    protected function sendError(string $message, array $errors = [], int $code = 404): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Send paginated response
     */
    public function sendPaginated($resourceCollection, $message): JsonResponse
    {
        $paginatedResponse = $resourceCollection->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginatedResponse['data'],
            'meta'    => $paginatedResponse['meta']
        ]);
    }

    /**
     * Get authenticated user's organization ID
     */
    protected function getOrganizationId()
    {
        return Auth::user()->organization_id;
    }
}
