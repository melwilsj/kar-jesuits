<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Jesuit;
use Illuminate\Http\Request;

class JesuitController extends BaseController
{
    public function getCurrentJesuit(Request $request)
    {
        $user = $request->user();
        $jesuit = $user->jesuit;

        if (!$jesuit) {
            return $this->errorResponse('No Jesuit profile found', [], 404);
        }

        $jesuit->load([
            'user:id,name,email,phone_number',
            'currentCommunity:id,name,code',
            'activeRoles' => function($query) {
                $query->where('is_active', true)
                    ->whereNull('end_date');
            },
            'activeRoles.roleType:id,name',
            'activeRoles.assignable:id,name',
            'province:id,name,code',
            'region:id,name,code',
            'formationStages' => function($query) {
                $query->orderBy('jesuit_formations.start_date', 'desc');
            },
            'documents' => function($query) use ($user) {
                if (!$user->hasRole('superadmin')) {
                    $query->where(function($q) use ($user) {
                        $q->where('visibility', 'public')
                          ->orWhere(function($q) use ($user) {
                              $q->where('visibility', 'private')
                                ->where('user_id', $user->id);
                          });
                    });
                }
            }
        ]);

        $data = [
            'id' => $jesuit->id,
            'name' => $jesuit->user->name,
            'code' => $jesuit->code,
            'category' => $jesuit->category,
            'photo_url' => $jesuit->photo_url,
            'email' => $jesuit->user->email,
            'phone_number' => $jesuit->user->phone_number,
            'dob' => $jesuit->dob,
            'joining_date' => $jesuit->joining_date,
            'priesthood_date' => $jesuit->priesthood_date,
            'final_vows_date' => $jesuit->final_vows_date,
            'academic_qualifications' => $jesuit->academic_qualifications,
            'publications' => $jesuit->publications,
            'current_community' => $jesuit->currentCommunity->name,
            'province' => $jesuit->province->code,
            'region' => $jesuit->region?->code,
            'roles' => $jesuit->activeRoles->map(function($role) {
                return [
                    'type' => $role->roleType->name,
                    'institution' => $role->assignable->name ?? null,
                ];
            }),
            'formation' => $jesuit->formationStages->map(function($stage) {
                return [
                    'stage' => $stage->name,
                    'start_date' => $stage->pivot->start_date,
                    'end_date' => $stage->pivot->end_date,
                    'current_year' => $stage->pivot->current_year,
                    'status' => $stage->pivot->status
                ];
            }),
            'documents' => $jesuit->documents->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'type' => $doc->type,
                    'url' => $doc->url
                ];
            })
        ];

        return $this->successResponse($data);
    }
}
