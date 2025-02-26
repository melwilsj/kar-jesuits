<?php

namespace App\Http\Controllers\Api;

use App\Models\Institution;
use Illuminate\Http\Request;

class InstitutionController extends BaseController
{
    public function index(Request $request)
    {
        $institutions = Institution::when(
            !$request->user()->hasRole('superadmin'),
            function ($query) use ($request) {
                if ($request->user()->hasRole('province_admin')) {
                    $query->whereHas('community', function ($q) use ($request) {
                        $q->whereIn('province_id', $request->user()->provinces->pluck('id'));
                    });
                } elseif ($request->user()->hasRole('region_admin')) {
                    $query->whereHas('community', function ($q) use ($request) {
                        $q->whereIn('region_id', $request->user()->regions->pluck('id'));
                    });
                } elseif ($request->user()->hasRole('community_superior')) {
                    $query->whereHas('community', function ($q) use ($request) {
                        $q->where('superior_id', $request->user()->id);
                    });
                }
            }
        )->with('community')->get();

        return $this->successResponse($institutions);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('manage_institutions')) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'community_id' => 'required|exists:communities,id',
            'type' => 'required|in:school,college,university,hostel,community_college,iti,parish,social_centre,farm,ngo,other',
            'description' => 'nullable|string',
            'contact_details' => 'required|array',
            'contact_details.phones' => 'required|array',
            'contact_details.emails' => 'required|array',
            'contact_details.fax' => 'nullable|string',
            'contact_details.website' => 'nullable|url',
            
            'student_demographics' => 'required_if:type,school,college,university,hostel,community_college,iti|array',
            'student_demographics.catholics' => 'required_with:student_demographics|integer',
            'student_demographics.other_christians' => 'required_with:student_demographics|integer',
            'student_demographics.non_christians' => 'required_with:student_demographics|integer',
            'student_demographics.boys' => 'required_with:student_demographics|integer',
            'student_demographics.girls' => 'required_with:student_demographics|integer',
            
            'staff_demographics' => 'required|array',
            'staff_demographics.jesuits' => 'required|integer',
            'staff_demographics.other_religious' => 'required|integer',
            'staff_demographics.catholics' => 'required|integer',
            'staff_demographics.others' => 'required|integer',
            
            'beneficiaries' => 'required_if:type,social_centre,parish|array',
            'address' => 'required|string',
            'diocese' => 'nullable|string',
            'taluk' => 'nullable|string',
            'district' => 'nullable|string',
            'state' => 'nullable|string'
        ]);

        $institution = Institution::create($validated);

        return $this->successResponse($institution, 'Institution created successfully', 201);
    }

    public function show(Request $request, Institution $institution)
    {
        if (!$request->user()->canAccessInstitution($institution)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse($institution->load('community'));
    }

    public function update(Request $request, Institution $institution)
    {
        if (!$request->user()->canManageInstitution($institution)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'community_id' => 'sometimes|exists:communities,id',
            'type' => 'sometimes|in:school,college,university,hostel,community_college,iti,parish,social_centre,farm,ngo,other',
            'description' => 'nullable|string',
            'contact_details' => 'sometimes|array',
            'contact_details.phones' => 'sometimes|array',
            'contact_details.emails' => 'sometimes|array',
            'contact_details.fax' => 'nullable|string',
            'contact_details.website' => 'nullable|url',
            
            'student_demographics' => 'sometimes|required_if:type,school,college,university,hostel,community_college,iti|array',
            'student_demographics.catholics' => 'sometimes|required_with:student_demographics|integer',
            'student_demographics.other_christians' => 'sometimes|required_with:student_demographics|integer',
            'student_demographics.non_christians' => 'sometimes|required_with:student_demographics|integer',
            'student_demographics.boys' => 'sometimes|required_with:student_demographics|integer',
            'student_demographics.girls' => 'sometimes|required_with:student_demographics|integer',
            
            'staff_demographics' => 'sometimes|required|array',
            'staff_demographics.jesuits' => 'sometimes|required|integer',
            'staff_demographics.other_religious' => 'sometimes|required|integer',
            'staff_demographics.catholics' => 'sometimes|required|integer',
            'staff_demographics.others' => 'sometimes|required|integer',
            
            'beneficiaries' => 'sometimes|required_if:type,social_centre,parish|array',
            'address' => 'sometimes|required|string',
            'diocese' => 'nullable|string',
            'taluk' => 'nullable|string',
            'district' => 'nullable|string',
            'state' => 'nullable|string'
        ]);

        $institution->update($validated);

        return $this->successResponse($institution, 'Institution updated successfully');
    }

    public function destroy(Request $request, Institution $institution)
    {
        if (!$request->user()->canManageInstitution($institution)) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $institution->delete();

        return $this->successResponse(null, 'Institution deleted successfully');
    }
} 