<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'city_id', 'category_id', 'bio', 'is_free', 'qr_code_path'])]
class MasterProfile extends Model
{
    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
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
}
