<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['name_ru', 'name_tm', 'is_active', 'parent_city_id'])]
class City extends Model
{
    public const CACHE_KEY = 'cities:active';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function isSatellite(): bool
    {
        return $this->parent_city_id !== null;
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_city_id');
    }

    public function satellites()
    {
        return $this->hasMany(self::class, 'parent_city_id');
    }
}
