<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JesuitMember extends Jesuit
{
    // Explicitly set the table name to match the parent model
    protected $table = 'jesuits';
    
    // Don't need to define fillable - we'll handle saving manually

    protected $with = ['user']; // Always eager load user

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'category',
        'is_external',
        'prefix_modifier',
        'dob',
        'joining_date',
        'priesthood_date',
        'final_vows_date',
        'photo_url',
        'notes',
        'languages',
        'academic_qualifications',
        'publications',
        'province_id',
        'region_id',
        'current_community_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'priesthood_date' => 'date',
        'final_vows_date' => 'date',
        'is_external' => 'boolean',
        'is_active' => 'boolean',
        'languages' => 'array',
        'academic_qualifications' => 'array',
        'publications' => 'array',
    ];

    // Accessors for user attributes
    public function getNameAttribute()
    {
        return $this->user->name;
    }
    
    public function getEmailAttribute()
    {
        return $this->user->email;
    }
    
    public function getPhoneNumberAttribute()
    {
        return $this->user->phone_number;
    }
    
    public function getAccountActiveAttribute()
    {
        return $this->user->is_active;
    }
    
    // Update the formationStages relationship for more robust error handling
    public function formationStages(): BelongsToMany
    {
        try {
            return $this->belongsToMany(FormationStage::class, 'jesuit_formations', 'jesuit_id', 'formation_stage_id')
                ->withPivot(['start_date', 'end_date', 'current_year', 'status', 'notes'])
                ->withTimestamps();
        } catch (\Exception $e) {
            Log::error('Error in formationStages relationship: ' . $e->getMessage());
            // Return an empty collection instead of failing
            return $this->belongsToMany(FormationStage::class, 'jesuit_formations', 'jesuit_id', 'formation_stage_id')
                ->whereRaw('1=0'); // This ensures an empty result
        }
    }
    
    // Add a safer method to access current formation
    public function getCurrentFormationAttribute()
    {
        try {
            $formation = $this->formationStages()
                ->wherePivot('status', 'active')
                ->orderByPivot('start_date', 'desc')
                ->first();
            return $formation;
        } catch (\Exception $e) {
            Log::error('Error in getCurrentFormationAttribute: ' . $e->getMessage());
            return null;
        }
    }
    
    // Override roleAssignments relationship with correct return type
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(RoleAssignment::class, 'jesuit_id');
    }
    
    // Override documents relationship with correct return type
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable', 'documentable_type', 'documentable_id');
    }
    
    // Override histories relationship with correct return type
    public function histories(): HasMany
    {
        return $this->hasMany(JesuitHistory::class, 'jesuit_id');
    }
    
    // Override the save method to handle both models
    public function save(array $options = [])
    {
        DB::transaction(function () use ($options) {
            // Save user data
            if ($this->user) {
                // Set user fields if they exist in attributes
                if (isset($this->attributes['name'])) {
                    $this->user->name = $this->attributes['name'];
                }
                if (isset($this->attributes['email'])) {
                    $this->user->email = $this->attributes['email'];
                }
                if (isset($this->attributes['phone_number'])) {
                    $this->user->phone_number = $this->attributes['phone_number'];
                }
                if (isset($this->attributes['account_active'])) {
                    $this->user->is_active = $this->attributes['account_active'];
                }
                $this->user->save();
            }
            
            // Save jesuit data
            parent::save($options);
        });
        
        return $this;
    }
    
    // Custom create method that handles both models
    public static function createWithUser(array $userData, array $jesuitData)
    {
        return DB::transaction(function () use ($userData, $jesuitData) {
            // Create user
            $user = User::create($userData);
            
            // Create jesuit with user_id
            $jesuitData['user_id'] = $user->id;
            $jesuit = static::create($jesuitData);
            
            return $jesuit;
        });
    }

    /**
     * Get the URL for the Jesuit's photo.
     * Accessor: $model->photo_url
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                Log::debug("Accessor photoUrl:get called. DB value: '{$value}'");

                if (empty($value)) {
                    Log::debug('Accessor photoUrl:get - No photo path value found.');
                    return null;
                }

                $disk = 'cloudflare';

                try {
                    if (!Storage::disk($disk)->exists($value)) {
                        Log::error("Accessor photoUrl:get - File does not exist on disk '{$disk}' at path: " . $value);
                        return null; // Or return a placeholder URL: asset('images/placeholder.jpg');
                    }

                    $temporaryUrl = Storage::disk($disk)->temporaryUrl(
                        $value,
                        now()->addMinutes(15)
                    );

                    Log::debug("Accessor photoUrl:get - Generated temporary URL for path '{$value}': " . $temporaryUrl);
                    return $temporaryUrl;

                } catch (\Exception $e) {
                    Log::error("Accessor photoUrl:get - Error generating temporary URL for path '{$value}'.", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString() // Keep trace for debugging this specific issue
                    ]);
                    return null; // Or return a placeholder URL: asset('images/placeholder.jpg');
                }
            },
            set: function ($value) {
                Log::info("Accessor photoUrl:set called. Value being set: '{$value}'");
                // Return the value to be stored in the 'photo_url' attribute in the database
                return $value;
            }
        );
    }

    // Let's also add a method to help debug upload issues
    protected static function booted()
    {
        parent::booted();
        
        static::updating(function ($model) {
            if ($model->isDirty('photo_url')) {
                Log::info('Jesuit photo_url updated', [
                    'old' => $model->getOriginal('photo_url'),
                    'new' => $model->photo_url,
                ]);
            }
        });
    }

    /**
     * Get the user associated with the Jesuit member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the province the Jesuit member belongs to.
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get the region the Jesuit member belongs to.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the current community of the Jesuit member.
     */
    public function currentCommunity(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'current_community_id');
    }
} 