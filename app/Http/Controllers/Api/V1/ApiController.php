<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    protected function successResponse($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = 'Error', $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    protected function authResponse($user, $token = null)
    {
        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'type' => $user->type,
                'is_active' => $user->is_active,
                'permissions' => $user->permissions
            ]
        ];

        if ($token) {
            $data['token'] = [
                'access_token' => $token,
                'token_type' => 'Bearer'
            ];
        }

        return $this->successResponse($data);
    }
} 