<?php

namespace App\Http\Controllers\Api;

use App\Models\{Province, Region, Community, Institution, User, Commission, Group};
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class SearchController extends BaseController
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2',
            'type' => 'required|in:provinces,regions,communities,institutions,users,commissions,groups',
            'filters' => 'nullable|array'
        ]);

        $results = match ($validated['type']) {
            'provinces' => $this->searchProvinces($request),
            'regions' => $this->searchRegions($request),
            'communities' => $this->searchCommunities($request),
            'institutions' => $this->searchInstitutions($request),
            'users' => $this->searchUsers($request),
            'commissions' => $this->searchCommissions($request),
            'groups' => $this->searchGroups($request),
        };

        return $this->successResponse($results);
    }

    private function searchProvinces(Request $request): Builder
    {
        return Province::where('name', 'like', "%{$request->query}%")
            ->orWhere('code', 'like', "%{$request->query}%")
            ->when(!$request->user()->hasRole('superadmin'), function ($query) use ($request) {
                $query->whereIn('id', $request->user()->provinces->pluck('id'));
            });
    }

    private function searchCommunities(Request $request): Builder
    {
        return Community::where('name', 'like', "%{$request->query}%")
            ->orWhere('code', 'like', "%{$request->query}%")
            ->orWhere('address', 'like', "%{$request->query}%")
            ->when(!$request->user()->hasRole('superadmin'), function ($query) use ($request) {
                $query->whereIn('id', $request->user()->communities->pluck('id'));
            });
    }

    // Similar implementations for other search methods...
} 