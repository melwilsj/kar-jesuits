<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use App\Models\Event;
use App\Models\Province;
use App\Models\Region;
use App\Models\Community;
use App\Models\Jesuit;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell';
    protected static ?string $navigationGroup = 'Events & Notifications';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpan(2),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'event' => 'Event',
                                'news' => 'News',
                                'announcement' => 'Announcement',
                                'birthday' => 'Birthday',
                                'feast_day' => 'Feast Day',
                                'death' => 'Death',
                                'other' => 'Other',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('event_id')
                            ->label('Related Event')
                            ->relationship('event', 'title', function (Builder $query) {
                                return $query->whereNotNull('title')
                                             ->where('title', '!=', '')
                                             ->whereNotNull('start_datetime');
                            })
                            ->getOptionLabelFromRecordUsing(fn (Event $record) => "{$record->title} ({$record->start_datetime->format('Y-m-d')})")
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\DateTimePicker::make('scheduled_for')
                            ->label('Schedule for')
                            ->helperText('If set, the notification will be sent at this time'),
                    ])->columns(2),
                
                Forms\Components\Card::make()
                    ->schema([
                        Placeholder::make('notification_details_heading')
                            ->label('Notification Details')
                            ->columnSpan(2),
                        
                        Forms\Components\CheckboxList::make('recipient_types')
                            ->label('Recipient Types')
                            ->options([
                                'all' => 'All Users',
                                'province' => 'By Province',
                                'region' => 'By Region',
                                'community' => 'By Community',
                                'user' => 'Specific Users',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('province_recipients', []);
                                $set('region_recipients', []);
                                $set('community_recipients', []);
                                $set('user_recipients', []);
                            })
                            ->columnSpan(2),
                        
                        Forms\Components\CheckboxList::make('province_recipients')
                            ->label('Provinces')
                            ->options(Province::pluck('name', 'id'))
                            ->hidden(fn (callable $get) => !in_array('province', $get('recipient_types') ?? []))
                            ->columnSpan(2),
                        
                        Forms\Components\CheckboxList::make('region_recipients')
                            ->label('Regions')
                            ->options(Region::pluck('name', 'id'))
                            ->hidden(fn (callable $get) => !in_array('region', $get('recipient_types') ?? []))
                            ->columnSpan(2),
                        
                        Forms\Components\CheckboxList::make('community_recipients')
                            ->label('Communities')
                            ->options(Community::pluck('name', 'id'))
                            ->hidden(fn (callable $get) => !in_array('community', $get('recipient_types') ?? []))
                            ->columnSpan(2),
                        
                        Forms\Components\CheckboxList::make('user_recipients')
                            ->label('Users')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->hidden(fn (callable $get) => !in_array('user', $get('recipient_types') ?? []))
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
                        'primary' => 'event',
                        'success' => 'news',
                        'warning' => 'announcement',
                        'danger' => 'death',
                        'secondary' => fn ($state) => in_array($state, ['birthday', 'feast_day', 'other']),
                    ]),
                
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Related Event')
                    ->placeholder('N/A'),
                
                Tables\Columns\TextColumn::make('scheduled_for')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not Scheduled'),
                
                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not Sent'),
                
                Tables\Columns\IconColumn::make('is_sent')
                    ->boolean()
                    ->label('Sent'),
                
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('N/A'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'event' => 'Event',
                        'news' => 'News',
                        'announcement' => 'Announcement',
                        'birthday' => 'Birthday',
                        'feast_day' => 'Feast Day',
                        'death' => 'Death',
                        'other' => 'Other',
                    ]),
                
                Tables\Filters\Filter::make('unsent')
                    ->label('Not Sent Yet')
                    ->query(fn (Builder $query) => $query->where('is_sent', false)),
                
                Tables\Filters\Filter::make('sent')
                    ->label('Already Sent')
                    ->query(fn (Builder $query) => $query->where('is_sent', true)),
                
                Tables\Filters\Filter::make('scheduled')
                    ->label('Scheduled & Unsent')
                    ->query(fn (Builder $query) => $query->whereNotNull('scheduled_for')->where('is_sent', false)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn (Notification $record): bool => !$record->is_sent),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('send_now')
                    ->label('Send Now')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function (Notification $record, FirebaseNotificationService $firebaseService) {
                        static::sendNotificationAction($record, $firebaseService);
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->visible(fn (Notification $record): bool => !$record->is_sent),
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
            'view' => Pages\ViewNotification::route('/{record}'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return cache()->remember('unsent_notifications_count', 60, function () {
             return static::getModel()::where('is_sent', false)->count();
        });
    }

    public static function boot()
    {
        parent::boot();

        static::saved(function () {
            cache()->forget('unsent_notifications_count');
        });

        static::deleted(function () {
            cache()->forget('unsent_notifications_count');
        });
    }

    public static function sendNotificationAction(Notification $notification, FirebaseNotificationService $firebaseService, bool $showFeedback = true): bool
    {
        if ($notification->is_sent) {
             if ($showFeedback) {
                FilamentNotification::make()
                    ->title('Already Sent')
                    ->body('This notification has already been sent.')
                    ->warning()
                    ->send();
            }
            return false;
        }

        $users = $notification->getRecipientUsers();

        if ($users->isEmpty()) {
            if ($showFeedback) {
                FilamentNotification::make()
                    ->title('No Recipients')
                    ->body('No active users found matching the recipient criteria.')
                    ->warning()
                    ->send();
            }
            return false;
        }

        $success = $firebaseService->sendToUsers($notification, $users);

        if ($success) {
            $notification->update([
                'is_sent' => true,
                'sent_at' => now(),
                'scheduled_for' => null,
            ]);
            if ($showFeedback) {
                FilamentNotification::make()
                    ->title('Notification Sent')
                    ->body("Successfully sent notification '{$notification->title}' to {$users->count()} user(s).")
                    ->success()
                    ->send();
            }
            return true;
        } else {
             if ($showFeedback) {
                FilamentNotification::make()
                    ->title('Sending Failed')
                    ->body("Failed to send notification '{$notification->title}'. Check system logs for details.")
                    ->danger()
                    ->send();
            }
            return false;
        }
    }
} 