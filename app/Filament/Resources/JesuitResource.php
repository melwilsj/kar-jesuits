<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JesuitResource\Pages;
use App\Models\User;
use App\Models\Province;
use App\Models\Jesuit;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasResourceScoping;
use Filament\Tables\Filters\SelectFilter;

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
                        Forms\Components\Select::make('user_id')
                            ->label('User Account')
                            ->options(function() {
                                return User::whereDoesntHave('jesuit')
                                    ->orWhere('id', fn ($query) => 
                                        $query->select('user_id')
                                            ->from('jesuits')
                                            ->whereColumn('users.id', 'jesuits.user_id')
                                    )
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email'),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->confirmed(),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->password()
                                    ->required(),
                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                return $action
                                    ->modalHeading('Create New User')
                                    ->modalWidth('md');
                            }),
                        
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20),
                        
                        Forms\Components\Select::make('category')
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
                Tables\Columns\TextColumn::make('jesuit.code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jesuit.category')
                    ->label('Type'),
                Tables\Columns\TextColumn::make('jesuit.province.name')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('jesuit.currentCommunity.name')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('jesuit.currentFormation.stage.name')
                    ->label('Formation Stage')
                    ->placeholder('N/A'),
            ])
            ->filters([
                SelectFilter::make('view_type')
                    ->label('View')
                    ->options([
                        'province' => 'Province Only',
                        'province_region' => 'Province + Regions',
                    ])
                    ->default('province_region')
                    ->visible(fn () => auth()->user()->isProvinceAdmin()),
                
                SelectFilter::make('type')
                    ->label('Category')
                    ->options([
                        'Bp' => 'Bishop',
                        'P' => 'Priest',
                        'S' => 'Scholastic',
                        'NS' => 'Novice Scholastic',
                        'F' => 'Brother'
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->whereHas('jesuit', function ($query) use ($data) {
                                $query->where('category', $data['value']);
                            });
                        }
                    }),
                
                SelectFilter::make('province')
                    ->label('Province')
                    ->options(function() {
                        return Province::pluck('name', 'id')->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->whereHas('jesuit', function ($query) use ($data) {
                                $query->where('province_id', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Only get users who have a jesuit record
        return parent::getEloquentQuery()->whereHas('jesuit');
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