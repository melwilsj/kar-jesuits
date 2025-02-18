<?php

namespace App\Observers;

use App\Models\ProvinceTransfer;

class ProvinceTransferObserver
{
    public function created(ProvinceTransfer $transfer): void
    {
        $jesuit = $transfer->jesuit;
        
        if ($transfer->from_province_id === $jesuit->province_id) {
            // Jesuit requesting to leave current province
            $jesuit->update(['prefix_modifier' => '-']);
        } else {
            // External Jesuit requesting to join
            $jesuit->update(['prefix_modifier' => '+']);
        }
    }

    public function updated(ProvinceTransfer $transfer): void
    {
        if ($transfer->isDirty('status')) {
            $jesuit = $transfer->jesuit;
            
            switch ($transfer->status) {
                case 'completed':
                    $jesuit->update([
                        'province_id' => $transfer->to_province_id,
                        'prefix_modifier' => null
                    ]);
                    break;
                    
                case 'rejected':
                    $jesuit->update(['prefix_modifier' => null]);
                    break;
                    
                case 'pending':
                    // Handle if status is changed back to pending
                    if ($transfer->from_province_id === $jesuit->province_id) {
                        $jesuit->update(['prefix_modifier' => '-']);
                    } else {
                        $jesuit->update(['prefix_modifier' => '+']);
                    }
                    break;
            }
        }
    }
} 