<?php

namespace App\Filament\Resources\JesuitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;
use App\Models\FormationStage;

class FormationHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'formationStages';
    protected static ?string $recordTitleAttribute = 'stage.name';

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            return parent::handleRecordCreation($data);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating formation history: ' . $e->getMessage());
            throw $e;
        }
    }

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
                    ->label('Formation Stage')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('current_year')
                    // Only show when stage exists and has years
                    ->visible(fn ($record) => $record && $record->stage && method_exists($record->stage, 'hasYears') && $record->stage->hasYears()),
                    
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