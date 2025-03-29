<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use App\Models\Province;
use App\Models\Region;
use App\Models\Community;
use App\Models\Jesuit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Support\Str;
use App\Filament\Resources\NotificationResource;
use Filament\Forms\Components\Section;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Events & Notifications';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\RichEditor::make('description')
                            ->maxLength(65535)
                            ->columnSpan(2),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'regular' => 'Regular',
                                'special' => 'Special',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('event_type')
                            ->label('Event Type')
                            ->helperText('e.g., Birthday, Jubilee, Seminar, Retreat, etc.')
                            ->maxLength(255),
                        
                        Forms\Components\DateTimePicker::make('start_datetime')
                            ->label('Start Date & Time')
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('end_datetime')
                            ->label('End Date & Time'),
                        
                        Forms\Components\TextInput::make('venue')
                            ->maxLength(255),
                        
                        Forms\Components\Toggle::make('is_public')
                            ->label('Public Event')
                            ->helperText('If checked, this event will be visible to all users')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('Recurring Event')
                            ->default(false)
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('recurrence_pattern')
                            ->label('Recurrence Pattern')
                            ->helperText('e.g., "Every Monday", "First Sunday of the month", etc.')
                            ->hidden(fn (callable $get) => !$get('is_recurring'))
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('event_scope_heading')
                            ->label('Event Scope')
                            ->columnSpan(2),
                        
                        Forms\Components\Select::make('province_id')
                            ->label('Province')
                            ->options(function() {
                                return Province::where('name', '!=', '')
                                    ->whereNotNull('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->reactive(),
                        
                        Forms\Components\Select::make('region_id')
                            ->label('Region')
                            ->options(function(callable $get) {
                                $provinceId = $get('province_id');
                                if (!$provinceId) {
                                    return Region::where('name', '!=', '')
                                        ->whereNotNull('name')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                
                                return Region::where('province_id', $provinceId)
                                    ->where('name', '!=', '')
                                    ->whereNotNull('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->reactive(),
                        
                        Forms\Components\Select::make('community_id')
                            ->label('Community')
                            ->options(function(callable $get) {
                                $regionId = $get('region_id');
                                if (!$regionId) {
                                    return Community::where('name', '!=', '')
                                        ->whereNotNull('name')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                }
                                
                                return Community::where('region_id', $regionId)
                                    ->where('name', '!=', '')
                                    ->whereNotNull('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable(),
                        
                        Forms\Components\Select::make('jesuit_id')
                            ->label('Jesuit')
                            ->options(function() {
                                return Jesuit::query()
                                    ->join('users', 'jesuits.user_id', '=', 'users.id')
                                    ->whereNotNull('users.name')
                                    ->where('users.name', '!=', '')
                                    ->pluck('users.name', 'jesuits.id')
                                    ->toArray();
                            })
                            ->searchable(),
                    ])->columns(2),
                    
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('event_attachments_heading')
                            ->label('Event Attachments')
                            ->columnSpan(2),
                            
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Attachments (Images and Documents)')
                            ->multiple()
                            ->enableOpen()
                            ->enableDownload()
                            ->columnSpan(2),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'regular',
                        'warning' => 'special',
                    ]),
                
                Tables\Columns\TextColumn::make('event_type')
                    ->label('Event Type'),
                
                Tables\Columns\TextColumn::make('start_datetime')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('venue'),
                
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean()
                    ->label('Public'),
                
                Tables\Columns\IconColumn::make('is_recurring')
                    ->boolean()
                    ->label('Recurring'),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By'),
            ])
            ->defaultSort('start_datetime', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'regular' => 'Regular',
                        'special' => 'Special',
                    ]),
                
                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming Events')
                    ->query(fn (Builder $query) => $query->where('start_datetime', '>', now())),
                
                Tables\Filters\Filter::make('past')
                    ->label('Past Events')
                    ->query(fn (Builder $query) => $query->where('start_datetime', '<', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('create_notification')
                    ->label('Send Notification')
                    ->icon('heroicon-o-bell')
                    ->url(function (Event $record) {
                        return NotificationResource::getUrl('create', [
                            'event_id' => $record->id
                        ]);
                    })
                    ->openUrlInNewTab(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'view' => Pages\ViewEvent::route('/{record}'),
        ];
    }
} 