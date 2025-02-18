<?php

namespace App\Filament\Resources\JesuitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;

class HistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';
    protected static ?string $recordTitleAttribute = 'status';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('community_id')
                    ->relationship('community', 'name')
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
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('community.name'),
                Tables\Columns\TextColumn::make('province.name'),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('start_date')->date(),
                Tables\Columns\TextColumn::make('end_date')->date(),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->defaultSort('start_date', 'desc');
    }
} 