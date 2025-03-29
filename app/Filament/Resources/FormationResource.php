<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormationResource\Pages;
use App\Models\JesuitFormation;
use App\Models\Jesuit;
use App\Models\FormationStage;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class FormationResource extends Resource
{
    protected static ?string $model = JesuitFormation::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Formation';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jesuit_id')
                    ->label('Jesuit')
                    ->options(function() {
                        return Jesuit::join('users', 'jesuits.user_id', '=', 'users.id')
                            ->select('jesuits.id', 'users.name')
                            ->orderBy('users.name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->required(),
                Forms\Components\TextInput::make('current_year')
                    ->numeric()
                    ->minValue(1)
                    ->visible(fn (callable $get) => 
                        FormationStage::find($get('stage_id'))?->hasYears()),
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\TextInput::make('status')
                    ->default('active'),
                Forms\Components\Textarea::make('notes')
                    ->rows(3),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jesuit.user.name')
                    ->label('Name')
                    ->searchable(['users.name']),
                Tables\Columns\TextColumn::make('jesuit.code')
                    ->label('Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_year'),
                Tables\Columns\TextColumn::make('start_date')
                    ->date(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'completed' => 'info',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->relationship('stage', 'name'),
                Tables\Filters\SelectFilter::make('jesuit')
                    ->label('Jesuit')
                    ->options(function() {
                        return Jesuit::join('users', 'jesuits.user_id', '=', 'users.id')
                            ->select('jesuits.id', 'users.name')
                            ->orderBy('users.name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->attribute('jesuit_id'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('jesuit_formations.*')
            ->join('jesuits', 'jesuit_formations.jesuit_id', '=', 'jesuits.id')
            ->join('users', 'jesuits.user_id', '=', 'users.id');
    }

    protected function getTableQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormations::route('/'),
            'create' => Pages\CreateFormation::route('/create'),
            'edit' => Pages\EditFormation::route('/{record}/edit'),
        ];
    }
} 