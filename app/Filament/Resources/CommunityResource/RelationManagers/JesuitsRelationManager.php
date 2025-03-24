<?php

namespace App\Filament\Resources\CommunityResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms;
use Filament\Tables;

class JesuitsRelationManager extends RelationManager
{
    protected static string $relationship = 'jesuits';
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('currentFormation.stage.name')
                    ->label('Formation Stage'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Add actions if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
} 