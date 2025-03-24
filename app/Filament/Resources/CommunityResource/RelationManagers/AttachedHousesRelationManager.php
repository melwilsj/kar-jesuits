<?php

namespace App\Filament\Resources\CommunityResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class AttachedHousesRelationManager extends RelationManager
{
    protected static string $relationship = 'attachedHouses';
    protected static ?string $title = 'Attached Houses';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\Select::make('region_id')
                            ->relationship('region', 'name')
                            ->nullable(),
                        Forms\Components\Toggle::make('is_attached_house')
                            ->default(true)
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('phone')
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->email(),
                        Forms\Components\TextInput::make('diocese')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('district')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(100),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('leader.user.name')
                    ->label('Coordinator')
                    ->description(fn ($record) => $record->superior_type ?? ''),
                Tables\Columns\TextColumn::make('jesuits_count')
                    ->label('Members')
                    ->counts('jesuits'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('region')
                    ->relationship('region', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['is_attached_house'] = true;
                        $data['province_id'] = $this->getOwnerRecord()->province_id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
} 