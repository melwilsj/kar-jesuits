<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Province;
use App\Models\Community;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'System Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Special User Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => 
                                filled($state) ? Hash::make($state) : null
                            )
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                            
                        TextInput::make('phone_number')
                            ->tel()
                            ->maxLength(20),
                        
                        // Only show select for non-admin users
                        Select::make('type')
                            ->options([
                                'user' => 'Regular User',
                                'admin' => 'Admin',
                                'superadmin' => 'Super Admin',
                            ])
                            ->default('user')
                            ->required(),
                            
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),
                
                // Only show these sections if needed
                Section::make('User Roles')
                    ->schema([
                        CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->columns(2)
                            ->helperText('Select roles for this user'),
                    ])
                    ->visible(fn (callable $get): bool => 
                        in_array($get('type'), ['admin', 'superadmin'])
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('phone_number'),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => 
                        match ($state) {
                            'superadmin' => 'danger',
                            'admin' => 'warning',
                            default => 'info',
                        }
                    ),
                    
                Tables\Columns\TextColumn::make('jesuit.province.name')
                    ->label('Province')
                    ->placeholder('N/A'),
                    
                Tables\Columns\TextColumn::make('jesuit.currentCommunity.name')
                    ->label('Community')
                    ->placeholder('N/A'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Modified filters to use proper relationships
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'user' => 'Regular User',
                        'admin' => 'Admin',
                        'superadmin' => 'Super Admin',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Only get users who do NOT have a jesuit record
        return parent::getEloquentQuery()->whereDoesntHave('jesuit');
    }
}
