<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = Auth::user();
        
        // Load additional relations if needed
        // $user->load(['roles', 'permissions']);
        
        return response()->json([
            'user' => $user
        ]);
    }
    
    /**
     * Update the authenticated user's profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id),
            ],
            'avatar' => 'sometimes|nullable|string|max:2048',
            // Add other fields as needed
        ]);
        
        $user->update($validated);
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
} 