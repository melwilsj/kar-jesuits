<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('create_notification')
                ->label('Send Notification')
                ->icon('heroicon-o-bell')
                ->url(function () {
                    // Use the correct route generation method from the NotificationResource
                    return NotificationResource::getUrl('create', [
                        'event_id' => $this->record->id
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Event Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('event_type'),
                        Infolists\Components\TextEntry::make('start_datetime')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('end_datetime')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('venue'),
                    ])->columns(2),
                
                Infolists\Components\Section::make('Event Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->html(),
                    ]),
                
                Infolists\Components\Section::make('Event Scope')
                    ->schema([
                        Infolists\Components\TextEntry::make('province.name'),
                        Infolists\Components\TextEntry::make('region.name'),
                        Infolists\Components\TextEntry::make('community.name'),
                    ])->columns(3),
            ]);
    }
} 