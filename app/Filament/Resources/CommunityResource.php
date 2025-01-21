<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Models\Community;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class CommunityResource extends Resource
{
    protected static ?string $model = Community::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Administration';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Select::make('province_id')
                            ->relationship('province', 'name')
                            ->required(),
                        Forms\Components\Select::make('region_id')
                            ->relationship('region', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('parent_community_id')
                            ->relationship('parentCommunity', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('superior_type')
                            ->options([
                                'rector' => 'Rector',
                                'superior' => 'Superior',
                                'coordinator' => 'Coordinator'
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_formation_house')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->columnSpan('full'),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('superior_type')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\IconColumn::make('is_formation_house')
                    ->boolean(),
                Tables\Columns\TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Members'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('province')
                    ->relationship('province', 'name'),
                Tables\Filters\SelectFilter::make('region')
                    ->relationship('region', 'name'),
                Tables\Filters\TernaryFilter::make('is_formation_house'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CommunityResource\RelationManagers\MembersRelationManager::class,
            CommunityResource\RelationManagers\InstitutionsRelationManager::class,
        ];
    }
} 