<?php

namespace App\Filament\Resources\JesuitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;
use App\Models\Community;
use App\Models\Institution;
use App\Models\Province;

class RoleAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'roleAssignments';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('role_type_id')
                    ->relationship('roleType', 'name')
                    ->required(),
                Forms\Components\Select::make('assignable_type')
                    ->options([
                        Community::class => 'Community',
                        Institution::class => 'Institution',
                        Province::class => 'Province'
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('assignable_id', null)),
                Forms\Components\Select::make('assignable_id')
                    ->options(function (callable $get) {
                        $type = $get('assignable_type');
                        if (!$type) return [];
                        
                        $model = app($type);
                        return $model::pluck('name', 'id')->toArray();
                    })
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('roleType.name'),
                Tables\Columns\TextColumn::make('assignable_type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                Tables\Columns\TextColumn::make('assignable.name')
                    ->label('Assigned to'),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('start_date', 'desc');
    }
} 