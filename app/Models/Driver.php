<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    /**
     * Ergast/Jolpica nationalities are English demonyms (e.g. "British",
     * not "GB"), so they need to be mapped by hand to a flag emoji.
     */
    private const FLAGS = [
        'American' => '🇺🇸', 'Argentine' => '🇦🇷', 'Australian' => '🇦🇺', 'Austrian' => '🇦🇹',
        'Belgian' => '🇧🇪', 'Brazilian' => '🇧🇷', 'British' => '🇬🇧', 'Canadian' => '🇨🇦',
        'Chilean' => '🇨🇱', 'Chinese' => '🇨🇳', 'Colombian' => '🇨🇴', 'Czech' => '🇨🇿',
        'Danish' => '🇩🇰', 'Dutch' => '🇳🇱', 'Finnish' => '🇫🇮', 'French' => '🇫🇷',
        'German' => '🇩🇪', 'Hungarian' => '🇭🇺', 'Indian' => '🇮🇳', 'Indonesian' => '🇮🇩',
        'Irish' => '🇮🇪', 'Italian' => '🇮🇹', 'Japanese' => '🇯🇵', 'Liechtensteiner' => '🇱🇮',
        'Malaysian' => '🇲🇾', 'Mexican' => '🇲🇽', 'Monegasque' => '🇲🇨', 'New Zealander' => '🇳🇿',
        'Polish' => '🇵🇱', 'Portuguese' => '🇵🇹', 'Rhodesian' => '🇿🇼', 'Russian' => '🇷🇺',
        'South African' => '🇿🇦', 'Spanish' => '🇪🇸', 'Swedish' => '🇸🇪', 'Swiss' => '🇨🇭',
        'Thai' => '🇹🇭', 'Uruguayan' => '🇺🇾', 'Venezuelan' => '🇻🇪', 'Emirati' => '🇦🇪',
        'Saudi Arabian' => '🇸🇦',
    ];

    protected $fillable = [
        'api_ref', 'first_name', 'last_name', 'code', 'number', 'nationality', 'date_of_birth',
        'photo_url', 'wikipedia_url', 'constructor_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function constructor(): BelongsTo
    {
        return $this->belongsTo(Constructor::class);
    }

    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    public function getFlagAttribute(): ?string
    {
        return self::FLAGS[$this->nationality] ?? null;
    }
}
