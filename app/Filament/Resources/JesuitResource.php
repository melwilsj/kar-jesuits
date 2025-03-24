<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JesuitResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasResourceScoping;
use Filament\Tables\Filters\Filter;

class JesuitResource extends Resource
{
    use HasResourceScoping;

    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Member Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone_number')
                            ->tel(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'Bp' => 'Bishop',
                                'P' => 'Priest',
                                'S' => 'Scholastic',
                                'NS' => 'Novice Scholastic',
                                'F' => 'Brother'
                            ])
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Formation Details')
                    ->schema([
                        Forms\Components\DatePicker::make('profile.dob')
                            ->required(),
                        Forms\Components\DatePicker::make('profile.joining_date')
                            ->required(),
                        Forms\Components\DatePicker::make('profile.priesthood_date'),
                        Forms\Components\DatePicker::make('profile.final_vows_date'),
                    ])->columns(2),

                Forms\Components\Section::make('Assignments')
                    ->schema([
                        Forms\Components\Select::make('province_id')
                            ->relationship('province', 'name')
                            ->required(),
                        Forms\Components\Select::make('current_community_id')
                            ->relationship('currentCommunity', 'name')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('province.name'),
                Tables\Columns\TextColumn::make('currentCommunity.name'),
                Tables\Columns\TextColumn::make('currentFormation.stage.name')
                    ->label('Formation Stage'),
            ])
            ->filters([
                Filter::make('view_type')
                    ->label('View')
                    ->select([
                        'province' => 'Province Only',
                        'province_region' => 'Province + Regions',
                    ])
                    ->default('province_region')
                    ->visible(fn () => auth()->user()->isProvinceAdmin()),
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('province_id')
                    ->relationship('province', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            JesuitResource\RelationManagers\FormationHistoryRelationManager::class,
            JesuitResource\RelationManagers\RoleAssignmentsRelationManager::class,
            JesuitResource\RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJesuits::route('/'),
            'create' => Pages\CreateJesuit::route('/create'),
            'view' => Pages\ViewJesuit::route('/{record}'),
            'edit' => Pages\EditJesuit::route('/{record}/edit'),
        ];
    }

    // Override for specific Jesuit scoping
    protected static function applyProvinceScope($query, $provinceId)
    {
        return $query->whereHas('jesuit', function($q) use ($provinceId) {
            $q->where('province_id', $provinceId);
        });
    }
} 