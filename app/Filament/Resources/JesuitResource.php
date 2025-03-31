<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JesuitResource\Pages;
use App\Models\User;
use App\Models\Province;
use App\Models\Jesuit;
use App\Models\FormationStage;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasResourceScoping;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\JesuitMember;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\ImageColumn;

class JesuitResource extends Resource
{
    use HasResourceScoping;

    protected static ?string $model = JesuitMember::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Member Management';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // USER ACCOUNT SECTION
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                            ->validationAttribute('email')
                            ->live()
                            ->afterStateUpdated(fn () => $form->validate()),
                        
                                Forms\Components\TextInput::make('password')
                                    ->password()
                            ->dehydrateStateUsing(fn ($state) => 
                                filled($state) ? Hash::make($state) : null
                            )
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->label(fn (string $operation): string => 
                                $operation === 'create' ? 'Password' : 'New Password (leave blank to keep current)'
                            ),
                        
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->maxLength(20),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Account')
                            ->default(true),
                    ])->columns(2),
                
                // JESUIT PROFILE SECTION
                Forms\Components\Section::make('Jesuit Profile')
                    ->schema([
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
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Jesuit Status')
                            ->default(true)
                            ->helperText('Separate from account status, indicates active ministry'),
                            
                        Forms\Components\Toggle::make('is_external')
                            ->label('External Member')
                            ->default(false),
                            
                        Forms\Components\TextInput::make('prefix_modifier')
                            ->label('Prefix Modifier')
                            ->helperText('*, +, - for province transfer status'),
                    ])->columns(2),
                
                // PERSONAL INFORMATION
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\DatePicker::make('dob')
                            ->label('Date of Birth')
                            ->required(),
                            
                        Forms\Components\DatePicker::make('joining_date')
                            ->label('Date of Joining')
                            ->required(),
                            
                        Forms\Components\DatePicker::make('priesthood_date')
                            ->label('Date of Ordination'),
                            
                        Forms\Components\DatePicker::make('final_vows_date')
                            ->label('Date of Final Vows'),
                            
                        Forms\Components\FileUpload::make('photo_url')
                            ->label('Photo')
                            ->image()
                            ->disk('cloudflare')
                            ->directory('jesuit-photos')
                            ->storeFileNamesIn('original_filename')
                            ->visibility('private')
                            ->preserveFilenames()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->loadingIndicatorPosition('center')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('center')
                            ->uploadProgressIndicatorPosition('center'),
                    ])->columns(2),

                // ASSIGNMENTS
                Forms\Components\Section::make('Assignments')
                    ->schema([
                        Forms\Components\Select::make('province_id')
                            ->relationship('province', 'name')
                            ->required(),
                            
                        Forms\Components\Select::make('region_id')
                            ->relationship('region', 'name')
                            ->searchable(),
                            
                        Forms\Components\Select::make('current_community_id')
                            ->relationship('currentCommunity', 'name')
                            ->required(),
                            
                        Forms\Components\TextInput::make('ministry')
                            ->label('Current Ministry')
                            ->helperText('Current chosen ministry/apostolate'),
                    ])->columns(2),
                    
                // ADDITIONAL INFORMATION
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                            
                        Forms\Components\TagsInput::make('languages')
                            ->splitKeys(['Tab', 'Enter', ','])
                            ->placeholder('Add a language'),
                            
                        Forms\Components\Repeater::make('academic_qualifications')
                            ->schema([
                                Forms\Components\TextInput::make('degree')
                                    ->required(),
                                Forms\Components\TextInput::make('field')
                                    ->required(),
                                Forms\Components\TextInput::make('year')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->itemLabel(fn (array $state): ?string => 
                                $state['degree'] ?? null
                                    ? "{$state['degree']} in {$state['field']} ({$state['year']})"
                                    : null
                            )
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Add Academic Qualification'),
                        
                        Forms\Components\Repeater::make('publications')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required(),
                                Forms\Components\Textarea::make('details')
                                    ->rows(2),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Add Publication'),
                    ])->columns(1)->collapsible(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->disk('cloudflare')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Always visible columns
                TextColumn::make('category')
                    ->label('Type')
                    ->sortable(),
                    
                TextColumn::make('name')
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('user', function($userQuery) use ($search) {
                            $userQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
                        });
                    }),
                    
                TextColumn::make('currentCommunity.name')
                    ->label('Community')
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('currentCommunity', function($communityQuery) use ($search) {
                            $communityQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
                        });
                    })
                    ->placeholder('Not Assigned')
                    ->sortable(),
                    
                TextColumn::make('phone_number')
                    ->label('Phone Number'),
                    
                TextColumn::make('email')
                    ->label('Email'),
                
                TextColumn::make('formationStages')
                    ->label('Formation')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function($record) {
                        $activeFormation = $record->formationStages()
                            ->wherePivot('status', 'active')
                            ->orderByPivot('start_date', 'desc')
                            ->first();
                        
                        if (!$activeFormation) {
                            return null;
                        }
                        
                        $formationText = $activeFormation->name;
                        
                        if ($activeFormation->hasYears()) {
                            $year = $record->formationStages()
                                ->wherePivot('formation_stage_id', $activeFormation->id)
                                ->wherePivot('status', 'active')
                                ->first()?->pivot?->current_year ?? '';
                                
                            if ($year) {
                                $formationText .= " ($year)";
                            }
                        }
                        
                        return $formationText;
                    })
                    ->placeholder('Not in Formation'),
                
                TextColumn::make('roleAssignments')
                    ->label('Roles')
                    ->width('250px')
                    ->html()
                    ->formatStateUsing(function($record) {
                        $activeRoles = $record->roleAssignments->where('is_active', true)->take(3);
                        
                        $roleTexts = $activeRoles->map(function($role) {
                            $entity = class_basename($role->assignable_type);
                            return "<span class='text-sm py-1'>{$role->roleType->name} ({$entity})</span>";
                        });
                        
                        $count = $record->roleAssignments->where('is_active', true)->count();
                        $moreText = $count > 3 ? "<span class='text-xs text-gray-500'>(+".($count-3)." more)</span>" : "";
                        
                        return $roleTexts->implode('<br>') . ($moreText ? '<br>' . $moreText : '');
                    }),
                    
                // Optional columns that can be toggled
                TextColumn::make('province.name')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('priesthood_date')
                    ->date('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('joining_date')
                    ->date('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('final_vows_date')
                    ->date('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('dob')
                    ->label('Date of Birth')
                    ->date('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Province view filter (Only show if user is province admin)
                TernaryFilter::make('include_region_members')
                    ->label('Include Region Members')
                    ->default(false)
                    ->visible(fn () => auth()->user()->isProvinceAdmin())
                    ->queries(
                        true: fn (Builder $query) => $query,
                        false: fn (Builder $query) => $query->whereNull('region_id'),
                        blank: fn (Builder $query) => $query->whereNull('region_id'),
                    ),
                
                // Category filter
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
                            return $query->where('category', $data['value']);
                        }
                    }),
                
                // Date of Birth filter
                Filter::make('dob')
                    ->form([
                        Forms\Components\DatePicker::make('dob_from')
                            ->label('Born from'),
                        Forms\Components\DatePicker::make('dob_until')
                            ->label('Born until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dob_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('dob', '>=', $date),
                            )
                            ->when(
                                $data['dob_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('dob', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['dob_from'] ?? null) {
                            $indicators[] = Indicator::make('Born from ' . Carbon::parse($data['dob_from'])->toFormattedDateString())
                                ->removeField('dob_from');
                        }
                        
                        if ($data['dob_until'] ?? null) {
                            $indicators[] = Indicator::make('Born until ' . Carbon::parse($data['dob_until'])->toFormattedDateString())
                                ->removeField('dob_until');
                        }
                        
                        return $indicators;
                    }),
                
                // Joining Date filter
                Filter::make('joining_date')
                    ->form([
                        Forms\Components\DatePicker::make('joining_from')
                            ->label('Joined from'),
                        Forms\Components\DatePicker::make('joining_until')
                            ->label('Joined until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['joining_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('joining_date', '>=', $date),
                            )
                            ->when(
                                $data['joining_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('joining_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['joining_from'] ?? null) {
                            $indicators[] = Indicator::make('Joined from ' . Carbon::parse($data['joining_from'])->toFormattedDateString())
                                ->removeField('joining_from');
                        }
                        
                        if ($data['joining_until'] ?? null) {
                            $indicators[] = Indicator::make('Joined until ' . Carbon::parse($data['joining_until'])->toFormattedDateString())
                                ->removeField('joining_until');
                        }
                        
                        return $indicators;
                    }),
                
                // Ordination Date filter
                Filter::make('priesthood_date')
                    ->form([
                        Forms\Components\DatePicker::make('ordained_from')
                            ->label('Ordained from'),
                        Forms\Components\DatePicker::make('ordained_until')
                            ->label('Ordained until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['ordained_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('priesthood_date', '>=', $date),
                            )
                            ->when(
                                $data['ordained_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('priesthood_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['ordained_from'] ?? null) {
                            $indicators[] = Indicator::make('Ordained from ' . Carbon::parse($data['ordained_from'])->toFormattedDateString())
                                ->removeField('ordained_from');
                        }
                        
                        if ($data['ordained_until'] ?? null) {
                            $indicators[] = Indicator::make('Ordained until ' . Carbon::parse($data['ordained_until'])->toFormattedDateString())
                                ->removeField('ordained_until');
                        }
                        
                        return $indicators;
                    }),
                
                // Final Vows Date filter
                Filter::make('final_vows_date')
                    ->form([
                        Forms\Components\DatePicker::make('vows_from')
                            ->label('Final vows from'),
                        Forms\Components\DatePicker::make('vows_until')
                            ->label('Final vows until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['vows_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('final_vows_date', '>=', $date),
                            )
                            ->when(
                                $data['vows_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('final_vows_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['vows_from'] ?? null) {
                            $indicators[] = Indicator::make('Final vows from ' . Carbon::parse($data['vows_from'])->toFormattedDateString())
                                ->removeField('vows_from');
                        }
                        
                        if ($data['vows_until'] ?? null) {
                            $indicators[] = Indicator::make('Final vows until ' . Carbon::parse($data['vows_until'])->toFormattedDateString())
                                ->removeField('vows_until');
                        }
                        
                        return $indicators;
                    }),
                
                // Province filter
                SelectFilter::make('province')
                    ->label('Province')
                    ->options(function() {
                        return Province::pluck('name', 'id')->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->where('province_id', $data['value']);
                        }
                    }),
                
                // Formation filter
                SelectFilter::make('formation_stage')
                    ->label('Formation Stage')
                    ->options(function() {
                        return FormationStage::pluck('name', 'id')->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->whereHas('formationStages', function ($query) use ($data) {
                                $query->where('formation_stages.id', $data['value'])
                                    ->wherePivot('status', 'active');
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
        $query = parent::getEloquentQuery();
        
        // If user is a province admin, always scope to their province
        if (auth()->user()->isProvinceAdmin()) {
            $provinceId = auth()->user()->jesuit->province_id ?? null;
            
            if ($provinceId) {
                $query->where('province_id', $provinceId);
            }
        }
        
        return $query;
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

    // Update the province scope method
    protected static function applyProvinceScope($query, $provinceId)
    {
        // Direct query on province_id since we're working with JesuitMember (which is a Jesuit)
        return $query->where('province_id', $provinceId);
    }

    // Add this method to customize validation
    public static function getEmailValidationRules($record = null): array
    {
        return [
            'email' => [
                'required', 
                'email',
                function ($attribute, $value, $fail) use ($record) {
                    // Custom validation to check email uniqueness in users table
                    $query = User::where('email', $value);
                    
                    // Exclude current user when editing
                    if ($record) {
                        $query->whereNot('id', $record->user_id);
                    }
                    
                    if ($query->exists()) {
                        $fail('This email is already taken.');
                    }
                }
            ],
        ];
    }
} 