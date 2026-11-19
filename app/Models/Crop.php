<?php

namespace App\Models;

use App\Models\Concerns\OwnedRecords;
use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    use OwnedRecords;

    protected $guarded = ['id'];

    public function plantings()
    {
        return $this->hasMany(Planting::class);
    }
}
