<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Jesuit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

        $photoUrl = null;
        if ($jesuit->photo_url) {
            try {
                $photoUrl = Storage::disk('cloudflare')->temporaryUrl(
                    $jesuit->photo_url,
                    now()->addDays(6)
                );
            } catch (\Exception $e) {
                Log::error("Failed to generate temporary URL for Jesuit photo: {$jesuit->photo_url}. Error: {$e->getMessage()}");
            }
        }

        $data = [
            'id' => $jesuit->id,
            'name' => $jesuit->user->name,
            'code' => $jesuit->code,
            'category' => $jesuit->category,
            'photo_url' => $photoUrl,
            'email' => $jesuit->user->email,
            'phone_number' => $jesuit->user->phone_number,
            'dob' => $jesuit->dob,
            'joining_date' => $jesuit->joining_date,
            'priesthood_date' => $jesuit->priesthood_date,
            'final_vows_date' => $jesuit->final_vows_date,
            'academic_qualifications' => $jesuit->academic_qualifications,
            'publications' => $jesuit->publications,
            'current_community' => $jesuit->currentCommunity?->name,
            'province' => $jesuit->province?->code,
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
                $docUrl = null;
                if ($doc->path) {
                    try {
                        $docUrl = Storage::disk('cloudflare')->temporaryUrl(
                            $doc->path,
                            now()->addDays(6)
                        );
                    } catch (\Exception $e) {
                        Log::error("Failed to generate temporary URL for document: {$doc->path}. Error: {$e->getMessage()}");
                    }
                }
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'type' => $doc->type,
                    'url' => $docUrl
                ];
            })
        ];

        return $this->successResponse($data);
    }
}
