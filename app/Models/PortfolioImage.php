<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['master_profile_id', 'image_path'])]
class PortfolioImage extends Model
{
    public function masterProfile()
    {
        return $this->belongsTo(MasterProfile::class);
    }
}
