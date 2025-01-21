<?php

namespace App\Http\Controllers\Api;

use App\Models\{Community, Institution, User, Commission, Group};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkOperationsController extends BaseController
{
    public function bulkDelete(Request $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:communities,institutions,commissions,groups',
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        try {
            DB::beginTransaction();

            $deletedCount = match ($validated['type']) {
                'communities' => $this->bulkDeleteCommunities($validated['ids'], $request),
                'institutions' => $this->bulkDeleteInstitutions($validated['ids'], $request),
                'commissions' => $this->bulkDeleteCommissions($validated['ids'], $request),
                'groups' => $this->bulkDeleteGroups($validated['ids'], $request),
            };

            DB::commit();
            return $this->successResponse(['deleted_count' => $deletedCount], 'Bulk delete successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Bulk delete failed: ' . $e->getMessage(), 500);
        }
    }

    private function bulkDeleteCommunities(array $ids, Request $request): int
    {
        $communities = Community::whereIn('id', $ids)
            ->get();

        $count = $communities->count();
        foreach ($communities as $community) {
            $community->delete();
        }
        return $count;
    }

    private function bulkDeleteInstitutions(array $ids, Request $request): int
    {
        $institutions = Institution::whereIn('id', $ids)
            ->when(!$request->user()->hasRole('superadmin'), function ($query) use ($request) {
                return $query->where('created_by', $request->user()->id);
            })
            ->get();
        $count = $institutions->count();
        foreach ($institutions as $institution) {
            $institution->delete();
        }
        return $count;
    }

    public function bulkUpdate(Request $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:communities,institutions,commissions,groups',
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'data' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            $updatedCount = match ($validated['type']) {
                'communities' => $this->bulkUpdateCommunities($validated['ids'], $validated['data'], $request),
                'institutions' => $this->bulkUpdateInstitutions($validated['ids'], $validated['data'], $request),
                'commissions' => $this->bulkUpdateCommissions($validated['ids'], $validated['data'], $request),
                'groups' => $this->bulkUpdateGroups($validated['ids'], $validated['data'], $request),
            };

            DB::commit();
            return $this->successResponse(['updated_count' => $updatedCount], 'Bulk update successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Bulk update failed: ' . $e->getMessage(), 500);
        }
    }

    private function bulkUpdateCommunities(array $ids, array $data, Request $request): int
    {
        $communities = Community::whereIn('id', $ids)
            ->when(!$request->user()->hasRole('superadmin'), function ($query) use ($request) {
                return $query->where('created_by', $request->user()->id);
            })->get();

        $count = 0;
        foreach ($communities as $community) {
            if ($community->update($data)) {
                $count++;
            }
        }
        return $count;
    }
} 