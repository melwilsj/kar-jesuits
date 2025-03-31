<?php

namespace App\Filament\Resources\JesuitResource\Pages;

use App\Filament\Resources\JesuitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use App\Models\JesuitMember;

class EditJesuit extends EditRecord
{
    protected static string $resource = JesuitResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        Log::debug('EditJesuit: Data before mutateFormDataBeforeFill', $data);

        // --- Start Photo URL Fix ---
        // Get the raw value directly from the database attribute, bypassing the accessor.
        // $this->record is the Eloquent model instance being edited.
        $rawPhotoPath = $this->record?->getAttributes()['photo_url'] ?? null;

        if ($rawPhotoPath) {
            // Explicitly set the 'photo_url' in the data array to the raw path.
            // This ensures the FileUpload component receives the path it needs.
            $data['photo_url'] = $rawPhotoPath;
            Log::info('EditJesuit mutateFormDataBeforeFill: Explicitly set photo_url to raw path: ' . $rawPhotoPath);
        } else {
            // Ensure it's null if no path exists, preventing potential issues.
            $data['photo_url'] = null;
            Log::info('EditJesuit mutateFormDataBeforeFill: No raw photo path found for record.');
        }
        // --- End Photo URL Fix ---

        // Log the state *after* potential modification
        Log::debug('EditJesuit: Data *after* mutateFormDataBeforeFill', $data);

        if (isset($data['id'])) {
            // Load the jesuit with all necessary relationships
            $jesuit = JesuitMember::with(['user', 'province', 'region', 'currentCommunity'])
                ->find($data['id']);
            
            if ($jesuit && $jesuit->user) {
                // Transfer user data to the form
                $data['name'] = $jesuit->user->name;
                $data['email'] = $jesuit->user->email;
                $data['phone_number'] = $jesuit->user->phone_number;
                $data['is_active'] = $jesuit->user->is_active;
                
                // Transfer jesuit data
                foreach ($jesuit->getAttributes() as $key => $value) {
                    if (!isset($data[$key]) && $key !== 'user_id') {
                        $data[$key] = $value;
                    }
                }
                
                // Format academic qualifications for the repeater component
                if ($jesuit->academic_qualifications) {
                    try {
                        $qualifications = is_string($jesuit->academic_qualifications) 
                            ? json_decode($jesuit->academic_qualifications, true) 
                            : $jesuit->academic_qualifications;
                            
                        if (is_array($qualifications)) {
                            // Make sure we have an array with numeric keys to work with the repeater
                            $data['academic_qualifications'] = array_values($qualifications);
                            Log::debug('Academic qualifications formatted', [
                                'original' => $jesuit->academic_qualifications,
                                'formatted' => $data['academic_qualifications']
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error formatting academic qualifications', [
                            'error' => $e->getMessage(),
                            'data' => $jesuit->academic_qualifications
                        ]);
                    }
                }
                
                // Handle languages the same way
                if ($jesuit->languages) {
                    $data['languages'] = is_string($jesuit->languages) 
                        ? json_decode($jesuit->languages, true) 
                        : $jesuit->languages;
                }
                
                // And publications
                if ($jesuit->publications) {
                    try {
                        $publications = is_string($jesuit->publications) 
                            ? json_decode($jesuit->publications, true) 
                            : $jesuit->publications;
                            
                        if (is_array($publications)) {
                            $data['publications'] = array_values($publications);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error formatting publications', [
                            'error' => $e->getMessage(),
                            'data' => $jesuit->publications
                        ]);
                    }
                }
            }
        }
        
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::debug('EditJesuit saving data:', $data);
        
        // Ensure the model correctly updates both the user and jesuit data
        if ($this->record && isset($data['id'])) {
            $jesuit = JesuitMember::find($data['id']);
            
            if ($jesuit && $jesuit->user) {
                // Update user attributes
                $jesuit->user->name = $data['name'] ?? $jesuit->user->name;
                $jesuit->user->email = $data['email'] ?? $jesuit->user->email;
                $jesuit->user->phone_number = $data['phone_number'] ?? $jesuit->user->phone_number;
                $jesuit->user->is_active = $data['is_active'] ?? $jesuit->user->is_active;
                
                // Handle password update (from JesuitResource form definition)
                if (!empty($data['password'])) {
                     $jesuit->user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
                }
                
                // Save the user
                $jesuit->user->save();
                
                // Log photo upload info if present
                if (isset($data['photo_url'])) {
                    Log::info('EditJesuit: Processing photo_url during save', [
                        'photo_url_data' => $data['photo_url'], // This will be the path string
                    ]);
                }
            }
        }
        
        // Remove password from data array so EditRecord doesn't try to save it directly on JesuitMember
        unset($data['password']);
        // Also remove user fields if they are not direct columns on JesuitMember
        unset($data['name'], $data['email'], $data['phone_number']);
        // You might need to unset other fields that belong to the user model
        
        return $data;
    }
    
    protected function fillForm(): void
    {
        try {
            parent::fillForm();
             // Log after filling to see the final state managed by the Form object
             Log::debug('EditJesuit: Form data state after parent::fillForm()', $this->form->getState());

        } catch (\Throwable $e) {
            Log::error('Error filling edit form: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            $this->redirect(static::getResource()::getUrl('index'));
            $this->notify('error', 'There was a problem editing this record: ' . $e->getMessage());
        }
    }
} 