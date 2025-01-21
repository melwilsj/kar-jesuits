<?php

namespace App\Filament\Resources\JesuitResource\Actions;

use App\Models\FormationStage;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class UpdateFormationAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'updateFormation';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Update Formation')
            ->form([
                Forms\Components\Select::make('stage_id')
                    ->label('Formation Stage')
                    ->options(FormationStage::orderBy('order')->pluck('name', 'id'))
                    ->required(),
                Forms\Components\TextInput::make('current_year')
                    ->numeric()
                    ->minValue(1)
                    ->visible(fn ($get) => FormationStage::find($get('stage_id'))?->has_years),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
            ])
            ->modalHeading('Update Formation Stage')
            ->modalButton('Update Formation')
            ->successNotification(
                notification: 'Formation stage updated successfully'
            )
            ->action(function (array $data, Model $record): void {
                // End current formation stage if one exists
                $record->currentFormation?->update(['end_date' => now()]);
                $record->formationHistory()->create($data);
            });
    }
} 