<?php

namespace App\Filament\Resources\JesuitResource\Pages;

use App\Filament\Resources\JesuitResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Filament\Notifications\Notification;
// Removed JesuitMember import as parent::handleRecordCreation uses static::getModel()

class CreateJesuit extends CreateRecord
{
    protected static string $resource = JesuitResource::class;

    /**
     * Override the default creation process to handle User and JesuitMember creation separately.
     */
    protected function handleRecordCreation(array $data): Model
    {
        Log::debug('CreateJesuit: Starting record creation with data:', $data);

        // 1. Prepare User data
        // Ensure 'is_active' defaults to true if not provided by the form
        $isActive = $data['is_active'] ?? true;

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'] ?? null,
            'password' => Hash::make($data['password']),
            'is_active' => $isActive,
            // --- IMPORTANT: Set the correct user type ---
            // Verify this value against your 'users_type_check' constraint in the database.
            'type' => 'jesuit',
            // ------------------------------------------
        ];

        Log::debug('CreateJesuit: Prepared user data:', $userData);

        // 2. Create the User within a transaction for safety
        $user = null;
        try {
            $user = User::create($userData);
            Log::info('CreateJesuit: User created successfully.', ['user_id' => $user->id]);
        } catch (\Throwable $e) {
            Log::error('CreateJesuit: Failed to create user.', [
                'error' => $e->getMessage(),
                'userData' => $userData,
                'trace' => $e->getTraceAsString(),
            ]);
            Notification::make()
                ->danger()
                ->title('User Creation Failed')
                ->body('Failed to create the associated user account: ' . $e->getMessage())
                ->send();
            throw $e;
        }

        // 3. Prepare JesuitMember data
        // Remove user-specific fields before passing data to the main model creation
        unset(
            $data['name'],
            $data['email'],
            $data['phone_number'],
            $data['password'],
            $data['password_confirmation'] // Also remove confirmation if present
        );

        // Add the user_id
        $data['user_id'] = $user->id;

        // Add this line to set is_active for the jesuit record too:
        $data['is_active'] = $isActive; // Reuse the same is_active value we set for the user

        // Ensure JSON fields are encoded if they are arrays (e.g., from Repeaters)
        // Add any other JSON fields specific to JesuitMember here
        foreach (['languages', 'academic_qualifications', 'publications'] as $jsonField) {
            if (isset($data[$jsonField]) && is_array($data[$jsonField])) {
                // Filter out potential empty repeater items before encoding
                 $filteredItems = array_filter($data[$jsonField], function($item) {
                     // This basic check assumes an empty item is just an empty array or all nulls.
                     // Adjust if your empty repeater items look different.
                     return !empty(array_filter((array)$item));
                 });
                 // Store null if filtering results in an empty array, otherwise encode.
                 $data[$jsonField] = !empty($filteredItems) ? json_encode(array_values($filteredItems)) : null;
            } elseif (!isset($data[$jsonField])) {
                 $data[$jsonField] = null; // Ensure it's explicitly null if not provided
            }
        }

        Log::debug('CreateJesuit: Prepared JesuitMember data:', $data);

        // 4. Create the JesuitMember record using the parent class logic
        // The parent method uses static::getModel() which resolves to JesuitMember
        try {
            // The parent method handles the actual creation using the prepared $data
            $jesuitMember = parent::handleRecordCreation($data);
            Log::info('CreateJesuit: JesuitMember created successfully.', ['jesuit_member_id' => $jesuitMember->id]);
            return $jesuitMember;

        } catch (\Throwable $e) { // Catch Throwable
             Log::error('CreateJesuit: Failed to create JesuitMember.', [
                'error' => $e->getMessage(),
                'jesuitData' => $data, // Log data that failed
                'trace' => $e->getTraceAsString(),
            ]);
             // Attempt to clean up the created user if JesuitMember creation fails (rollback)
             if ($user) {
                 try {
                     $user->delete();
                     Log::warning('CreateJesuit: Rolled back user creation due to JesuitMember creation failure.', ['user_id' => $user->id]);
                 } catch (\Throwable $cleanupEx) { // Catch Throwable
                     Log::error('CreateJesuit: Failed to clean up user after JesuitMember creation failure.', [
                         'user_id' => $user->id,
                         'cleanup_error' => $cleanupEx->getMessage(),
                     ]);
                 }
             }
            Notification::make()
                ->danger()
                ->title('Jesuit Profile Creation Failed')
                ->body('Failed to create the Jesuit profile details: ' . $e->getMessage())
                ->send();
             throw $e; // Re-throw the original exception
        }
    }

    // Optional: Redirect after creation to the index page
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Optional: Customize success notification
     protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Jesuit member created successfully.');
    }
} 