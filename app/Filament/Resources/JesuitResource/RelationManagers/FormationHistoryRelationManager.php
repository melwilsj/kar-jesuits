<?php

namespace App\Filament\Resources\JesuitResource\RelationManagers;

use App\Models\FormationStage;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;

class FormationHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'formationHistory';
    protected static ?string $recordTitleAttribute = 'stage.name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->required(),
                Forms\Components\TextInput::make('current_year')
                    ->numeric()
                    ->minValue(1)
                    ->visible(fn (callable $get) => 
                        FormationStage::find($get('stage_id'))?->hasYears()),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stage.name'),
                Tables\Columns\TextColumn::make('current_year')
                    ->visible(fn ($record) => $record->stage->hasYears()),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
            ])
            ->defaultSort('start_date', 'desc');
    }
} 