<?php

namespace App\Filament\Resources\CommunityResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';
    protected static ?string $title = 'Members';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('role_type')
                    ->options([
                        'superior' => 'Superior',
                        'minister' => 'Minister',
                        'treasurer' => 'Treasurer',
                        'member' => 'Member'
                    ])
                    ->required(),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('currentFormation.stage.name')
                    ->label('Formation Stage'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
            ]);
    }
} 