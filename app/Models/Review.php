<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['client_id', 'master_profile_id', 'rating', 'comment'])]
class Review extends Model
{
    protected $attributes = [
        'moderation_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function masterProfile()
    {
        return $this->belongsTo(MasterProfile::class);
    }

    public function approve(): void
    {
        $this->forceFill(['moderation_status' => 'approved'])->save();
        $this->masterProfile->recalculateRating();
    }

    public function reject(): void
    {
        $this->forceFill(['moderation_status' => 'rejected'])->save();
        $this->masterProfile->recalculateRating();
    }
}
