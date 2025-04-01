<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstitutionResource\Pages;
use App\Filament\Resources\InstitutionResource\RelationManagers;
use App\Models\Institution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Traits\HasResourceScoping;
use Filament\Forms\Components\TagsInput;

class InstitutionResource extends Resource
{
    use HasResourceScoping;

    protected static ?string $model = Institution::class;
    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(Institution::class, 'code', ignoreRecord: true),
                        Forms\Components\Select::make('community_id')
                            ->relationship('community', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('code')->required()->unique(),
                            ]),
                        Forms\Components\Select::make('type')
                            ->required()
                            ->options([
                                'school' => 'School',
                                'college' => 'College',
                                'university' => 'University',
                                'hostel' => 'Hostel',
                                'community_college' => 'Community College',
                                'iti' => 'ITI',
                                'parish' => 'Parish',
                                'social_center' => 'Social Center',
                                'farm' => 'Farm',
                                'ngo' => 'NGO',
                                'retreat_center' => 'Retreat Center',
                                'other' => 'Other',
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Address Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('diocese')->maxLength(255),
                        Forms\Components\TextInput::make('taluk')->maxLength(255),
                        Forms\Components\TextInput::make('district')->maxLength(255),
                        Forms\Components\TextInput::make('state')->maxLength(255),
                    ]),

                Forms\Components\Section::make('Contact Details')
                    ->schema([
                        TagsInput::make('contact_details.phones')
                            ->label('Phone Numbers')
                            ->placeholder('Add phone number and press Enter')
                            ->splitKeys(['Enter', ','])
                            ->columnSpanFull(),

                        TagsInput::make('contact_details.emails')
                            ->label('Email Addresses')
                            ->placeholder('Add email address and press Enter')
                            ->splitKeys(['Enter', ','])
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('contact_details.website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(1),

                Forms\Components\Section::make('Student Demographics')
                    ->description('Enter counts for student categories.')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('student_demographics.catholics')
                                    ->label('Catholics')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('student_demographics.other_christians')
                                    ->label('Other Christians')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('student_demographics.non_christians')
                                    ->label('Non-Christians')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('student_demographics.boys')
                                    ->label('Boys')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('student_demographics.girls')
                                    ->label('Girls')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('student_demographics.total')
                                    ->label('Total Students')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Ensure this matches the sum of other categories if applicable.'),
                            ]),
                    ])->collapsible(),

                Forms\Components\Section::make('Staff Demographics')
                    ->description('Enter counts for staff categories.')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('staff_demographics.jesuits')
                                    ->label('Jesuits')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('staff_demographics.other_religious')
                                    ->label('Other Religious')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                                Forms\Components\TextInput::make('staff_demographics.catholics')
                                     ->label('Catholic Lay Staff')
                                     ->numeric()
                                     ->default(0)
                                     ->minValue(0),
                                Forms\Components\TextInput::make('staff_demographics.others')
                                     ->label('Other Lay Staff')
                                     ->numeric()
                                     ->default(0)
                                     ->minValue(0),
                                Forms\Components\TextInput::make('staff_demographics.total')
                                     ->label('Total Staff')
                                     ->numeric()
                                     ->default(0)
                                     ->minValue(0)
                                     ->helperText('Ensure this matches the sum if applicable.'),
                            ]),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('community.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('community')
                    ->relationship('community', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'school' => 'School',
                        'college' => 'College',
                        'university' => 'University',
                        'hostel' => 'Hostel',
                        'community_college' => 'Community College',
                        'iti' => 'ITI',
                        'parish' => 'Parish',
                        'social_center' => 'Social Center',
                        'farm' => 'Farm',
                        'ngo' => 'NGO',
                        'retreat_center' => 'Retreat Center',
                        'other' => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['community'])->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstitutions::route('/'),
            'create' => Pages\CreateInstitution::route('/create'),
            'view' => Pages\ViewInstitution::route('/{record}'),
            'edit' => Pages\EditInstitution::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
