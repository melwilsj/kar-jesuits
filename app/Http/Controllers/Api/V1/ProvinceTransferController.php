<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\{User, ProvinceTransfer};
use Illuminate\Http\Request;

class ProvinceTransferController extends BaseController
{
    public function request(Request $request, User $user)
    {
        if (!$request->user()->hasRole(['superadmin', 'province_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'to_province_id' => 'required|exists:provinces,id',
            'notes' => 'nullable|string'
        ]);

        $transfer = ProvinceTransfer::create([
            'user_id' => $user->id,
            'from_province_id' => $user->province_id,
            'to_province_id' => $validated['to_province_id'],
            'status' => 'pending',
            'request_date' => now(),
            'notes' => $validated['notes']
        ]);

        return $this->successResponse(
            $transfer->load(['fromProvince', 'toProvince']), 
            'Transfer request created successfully'
        );
    }

    public function updateStatus(Request $request, ProvinceTransfer $transfer)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,completed',
            'notes' => 'nullable|string'
        ]);

        $transfer->update([
            'status' => $validated['status'],
            'completion_date' => $validated['status'] === 'completed' ? now() : null,
            'notes' => $validated['notes'] ?? $transfer->notes
        ]);

        if ($validated['status'] === 'completed') {
            $transfer->user->update([
                'province_id' => $transfer->to_province_id,
                'region_id' => null // Reset region when changing province
            ]);
        }

        return $this->successResponse($transfer, 'Transfer status updated successfully');
    }
} 