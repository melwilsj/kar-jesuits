<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'type' => $this->type,
            'is_active' => $this->is_active,
            'jesuit' => $this->when($this->jesuit, function() {
                return [
                    'id' => $this->jesuit->id,
                    'code' => $this->jesuit->code,
                    'category' => $this->jesuit->category,
                    'current_community' => $this->jesuit->currentCommunity ? [
                        'id' => $this->jesuit->currentCommunity->id,
                        'name' => $this->jesuit->currentCommunity->name,
                        'code' => $this->jesuit->currentCommunity->code,
                    ] : null,
                    'active_roles' => $this->jesuit->activeRoles->map(function($role) {
                        return [
                            'id' => $role->id,
                            'type' => $role->roleType->name,
                            'assignable_type' => $role->assignable_type,
                            'start_date' => $role->start_date,
                        ];
                    }),
                ];
            }),
            'permissions' => $this->getPermissions(),
        ];
    }
} 