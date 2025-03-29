<?php

namespace App\Filament\Resources\JesuitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->required()
                    ->disk('documents'),
                Forms\Components\Toggle::make('is_private')
                    ->default(true),
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\IconColumn::make('is_private')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ]);
    }
} 