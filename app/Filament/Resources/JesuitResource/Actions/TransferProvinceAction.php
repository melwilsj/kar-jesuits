<?php

namespace App\Filament\Resources\JesuitResource\Actions;

use App\Models\Province;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class TransferProvinceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'transfer';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Transfer Province')
            ->form([
                Forms\Components\Select::make('to_province_id')
                    ->label('To Province')
                    ->options(Province::pluck('name', 'id'))
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
            ])
            ->modalHeading('Transfer Member to Another Province')
            ->modalButton('Submit Transfer Request')
            ->successNotification(
                notification: 'Transfer request submitted successfully'
            )
            ->action(function (array $data, Model $record): void {
                $record->provinceTransfers()->create([
                    'to_province_id' => $data['to_province_id'],
                    'from_province_id' => $record->province_id,
                    'status' => 'pending',
                    'request_date' => now(),
                    'notes' => $data['notes']
                ]);
            });
    }
} 