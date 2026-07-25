<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'city_id', 'category_id', 'bio', 'is_free', 'qr_code_path'])]
class MasterProfile extends Model
{
    /**
     * Совпадает с default() в миграции — без этого Eloquent не знает про
     * DB-side default и возвращает null в свежесозданном инстансе до fresh().
     */
    protected $attributes = [
        'moderation_status' => 'pending',
        'avg_rating' => 0,
        'reviews_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'avg_rating' => 'float',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function portfolioImages()
    {
        return $this->hasMany(PortfolioImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approve(): void
    {
        $this->forceFill(['moderation_status' => 'approved'])->save();
    }

    public function reject(): void
    {
        $this->forceFill(['moderation_status' => 'rejected'])->save();
    }

    public function recalculateRating(): void
    {
        $stats = $this->reviews()
            ->where('moderation_status', 'approved')
            ->selectRaw('COUNT(*) as count, COALESCE(AVG(rating), 0) as avg')
            ->first();

        $this->forceFill([
            'avg_rating' => round((float) $stats->avg, 2),
            'reviews_count' => (int) $stats->count,
        ])->save();
    }
}
