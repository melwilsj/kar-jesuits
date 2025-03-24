<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JesuitResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'code' => $this->code,
            'category' => $this->category, // P, S, B etc
            'phone_number' => $this->user->phone_number,
            'email' => $this->user->email,
            
            // Community info
            'current_community' => $this->whenLoaded('currentCommunity', function() {
                return [
                    'id' => $this->currentCommunity->id,
                    'name' => $this->currentCommunity->name,
                    'code' => $this->currentCommunity->code,
                    'address' => $this->currentCommunity->address,
                ];
            }),
            
            // Roles
            'active_roles' => $this->whenLoaded('activeRoles', function() {
                return $this->activeRoles->map(function($role) {
                    return [
                        'type' => $role->roleType->name,
                        'institution' => $role->assignable->name ?? null,
                        'start_date' => $role->start_date,
                    ];
                });
            }),
            
            // Formation
            'formation' => $this->whenLoaded('formationStages', function() {
                return [
                    'current_stage' => $this->currentFormation?->name,
                    'history' => $this->formationStages->map(function($stage) {
                        return [
                            'stage' => $stage->name,
                            'start_date' => $stage->pivot->start_date,
                            'end_date' => $stage->pivot->end_date,
                            'current_year' => $stage->pivot->current_year,
                            'status' => $stage->pivot->status
                        ];
                    })
                ];
            }),
            
            // Documents
            'documents' => $this->whenLoaded('documents', function() {
                return $this->documents->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'type' => $doc->type,
                        'url' => $doc->url,
                    ];
                });
            }),
        ];
    }
} 