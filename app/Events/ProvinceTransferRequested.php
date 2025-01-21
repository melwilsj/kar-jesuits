<?php

namespace App\Events;

use App\Models\ProvinceTransfer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProvinceTransferRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ProvinceTransfer $transfer
    ) {}
} 