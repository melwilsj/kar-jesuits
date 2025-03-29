<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('impersonate')
                ->label('Login as User')
                ->icon('heroicon-o-identification')
                ->visible(fn () => auth()->user()->type === 'superadmin' && auth()->id() !== $this->record->id)
                ->action(function () {
                    // Store the admin's ID in the session so they can go back
                    session()->put('admin_user_id', auth()->id());
                    // Login as the user
                    auth()->login($this->record);
                    // Redirect to dashboard
                    return redirect()->route('filament.admin.pages.dashboard');
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('User updated')
            ->body('The user has been updated successfully.');
    }
}
