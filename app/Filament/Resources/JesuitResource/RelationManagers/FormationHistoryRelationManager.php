<?php

namespace App\Filament\Resources\JesuitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;

class FormationHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'jesuit.formationHistory';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('formation_stage_id')
                    ->relationship('stage', 'name')
                    ->required(),
                Forms\Components\TextInput::make('current_year')
                    ->numeric()
                    ->minValue(1)
                    ->visible(fn (callable $get) => 
                        FormationStage::find($get('formation_stage_id'))?->hasYears()),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\TextInput::make('status')
                    ->default('active'),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stage.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_year')
                    ->visible(fn ($record) => $record->stage->hasYears()),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        default => 'warning',
                    }),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
} 