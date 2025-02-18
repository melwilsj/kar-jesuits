<?php

namespace App\Filament\Resources\JesuitResource\Actions;

use Filament\Forms;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class UpdateFormationAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'updateHistory';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Update History')
            ->form([
                Forms\Components\Select::make('community_id')
                    ->relationship('currentCommunity', 'name')
                    ->required(),
                Forms\Components\Select::make('province_id')
                    ->relationship('province', 'name')
                    ->required(),
                Forms\Components\Select::make('category')
                    ->options([
                        'Bp' => 'Bishop',
                        'P' => 'Priest',
                        'S' => 'Scholastic',
                        'NS' => 'Novice',
                        'F' => 'Brother'
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\TextInput::make('status'),
                Forms\Components\Textarea::make('remarks')
            ])
            ->modalHeading('Update History')
            ->modalButton('Update History')
            ->successNotification(
                notification: 'History updated successfully'
            )
            ->action(function (array $data, Model $record): void {
                $record->histories()->create($data);
            });
    }
} 