<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunityResource\Pages;
use App\Filament\Resources\CommunityResource\RelationManagers;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\HasResourceScoping;

class CommunityResource extends Resource
{
    use HasResourceScoping;

    protected static ?string $model = Community::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Institution Management';

    public static function form(Form $form): Form
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
                        Forms\Components\Select::make('province_id')
                            ->relationship('province', 'name')
                            ->required(),
                        Forms\Components\Select::make('region_id')
                            ->relationship('region', 'name')
                            ->nullable(),
                        Forms\Components\Select::make('parent_community_id')
                            ->relationship('parentCommunity', 'name')
                            ->nullable()
                            ->label('Parent Community'),
                        Forms\Components\TextInput::make('superior_type')
                            ->maxLength(50),
                    ])->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('diocese')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('taluk')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('district')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('state')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Community Type')
                    ->schema([
                        Forms\Components\Toggle::make('is_formation_house')
                            ->label('Formation House'),
                        Forms\Components\Toggle::make('is_attached_house')
                            ->label('Attached House'),
                        Forms\Components\Toggle::make('is_common_house')
                            ->label('Common House'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jesuits_count')
                    ->label('Members')
                    ->counts('jesuits'),
                Tables\Columns\TextColumn::make('institutions_count')
                    ->label('Institutions')
                    ->counts('institutions'),
                Tables\Columns\IconColumn::make('is_formation_house')
                    ->label('Formation')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('province_id')
                    ->relationship('province', 'name')
                    ->label('Province'),
                Tables\Filters\SelectFilter::make('region_id')
                    ->relationship('region', 'name')
                    ->label('Region'),
                Tables\Filters\TernaryFilter::make('is_formation_house')
                    ->label('Formation House'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\JesuitsRelationManager::class,
            RelationManagers\InstitutionsRelationManager::class,
            RelationManagers\AttachedHousesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommunities::route('/'),
            'create' => Pages\CreateCommunity::route('/create'),
            'view' => Pages\ViewCommunity::route('/{record}'),
            'edit' => Pages\EditCommunity::route('/{record}/edit'),
        ];
    }

    // Override for specific Community scoping
    protected static function applyProvinceScope($query, $provinceId)
    {
        return $query->where('province_id', $provinceId);
    }
}
