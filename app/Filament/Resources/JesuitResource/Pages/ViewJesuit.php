<?php

namespace App\Filament\Resources\JesuitResource\Pages;

use App\Filament\Resources\JesuitResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;
use Filament\Forms;
use App\Models\Province;
use App\Models\Community;
use App\Models\Region;
use App\Models\JesuitMember;
use Illuminate\Support\HtmlString;
use Filament\Actions\EditAction;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewJesuit extends ViewRecord
{
    protected static string $resource = JesuitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->color('primary')
                ->icon('heroicon-o-pencil-square'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Components\Section::make('Profile')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\Group::make()
                                    ->schema([
                                        Components\TextEntry::make('user.name')->label('Name'),
                                        Components\TextEntry::make('user.email')->label('Email'),
                                        Components\TextEntry::make('user.phone_number')->label('Phone'),
                                        Components\TextEntry::make('code'),
                                        Components\TextEntry::make('category')
                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                'Bp' => 'Bishop',
                                                'P' => 'Priest',
                                                'S' => 'Scholastic',
                                                'NS' => 'Novice Scholastic',
                                                'F' => 'Brother',
                                                default => $state,
                                            }),
                                        Components\IconEntry::make('user.is_active')->label('Account Active')->boolean(),
                                        Components\IconEntry::make('is_active')->label('Jesuit Status Active')->boolean(),
                                        Components\IconEntry::make('is_external')->label('External Member')->boolean(),
                                    ])->columnSpan(2),

                                Components\ImageEntry::make('photo_url')
                                    ->label('Photo')
                                    ->disk('cloudflare')
                                    ->height(150)
                                    ->circular()
                                    ->columnSpan(1),
                            ]),
                    ]),

                Components\Section::make('Personal Information')
                    ->schema([
                        Components\TextEntry::make('dob')->label('Date of Birth')->date(),
                        Components\TextEntry::make('joining_date')->label('Date of Joining')->date(),
                        Components\TextEntry::make('priesthood_date')->label('Date of Ordination')->date(),
                        Components\TextEntry::make('final_vows_date')->label('Date of Final Vows')->date(),
                        Components\TextEntry::make('languages')
                            ->label('Languages')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) return 'No languages recorded';
                                if (is_array($state)) return implode(', ', $state);
                                Log::warning("ViewJesuit languages formatStateUsing: state was not an array.", ['state' => $state]);
                                return is_string($state) ? $state : 'Invalid language data';
                            }),
                    ])->columns(2),

                Components\Section::make('Assignments')
                    ->schema([
                        Components\TextEntry::make('province.name')->label('Province'),
                        Components\TextEntry::make('region.name')->label('Region'),
                        Components\TextEntry::make('currentCommunity.name')->label('Current Community'),
                    ])->columns(2),
            ]);
    }

    protected function fillForm(): void
    {
        try {
            parent::fillForm();

            if ($this->record) {
                 Log::debug('ViewJesuit Record Data after fillForm:', [
                    'id' => $this->record->id,
                    'raw_photo_url' => $this->record->getAttributes()['photo_url'] ?? 'Not Set',
                    'raw_languages' => $this->record->getAttributes()['languages'] ?? 'Not Set',
                    'accessed_photo_url' => $this->record->photo_url,
                    'accessed_languages' => $this->record->languages,
                 ]);
            }

        } catch (\Throwable $e) {
            Log::error('Error during ViewJesuit fillForm: ' . $e->getMessage(), [
                 'exception' => $e,
                 'trace' => $e->getTraceAsString()
            ]);
            $this->redirect(static::getResource()::getUrl('index'));
            $this->notify('error', 'There was a problem viewing this record.');
        }
    }
} 