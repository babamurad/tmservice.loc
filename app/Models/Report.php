<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['reporter_id', 'master_profile_id', 'reason'])]
class Report extends Model
{
    protected $attributes = [
        'status' => 'pending',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function masterProfile()
    {
        return $this->belongsTo(MasterProfile::class);
    }

    public function resolve(): void
    {
        $this->forceFill(['status' => 'resolved'])->save();
    }

    public function dismiss(): void
    {
        $this->forceFill(['status' => 'dismissed'])->save();
    }
}
