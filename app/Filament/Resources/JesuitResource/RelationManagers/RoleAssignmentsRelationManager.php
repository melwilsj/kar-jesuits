<?php

namespace App\Filament\Resources\JesuitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;

class RoleAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'jesuit.roleAssignments';
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
                        'community' => 'Community',
                        'institution' => 'Institution',
                        'province' => 'Province'
                    ])
                    ->required(),
                Forms\Components\Select::make('assignable_id')
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('roleType.name'),
                Tables\Columns\TextColumn::make('assignable_type'),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('start_date', 'desc');
    }
} 