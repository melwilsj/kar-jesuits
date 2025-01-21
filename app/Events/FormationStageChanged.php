<?php

namespace App\Events;

use App\Models\JesuitFormation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FormationStageChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public JesuitFormation $formation,
        public ?JesuitFormation $previousFormation = null
    ) {}
} 