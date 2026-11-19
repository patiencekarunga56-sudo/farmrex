<?php

namespace App\Models;

use App\Models\Concerns\OwnedRecords;
use Illuminate\Database\Eloquent\Model;

class Observation extends Model
{
    use OwnedRecords;

    protected $guarded = ['id'];

    protected $casts = ['observed_on' => 'date'];

    public function planting()
    {
        return $this->belongsTo(Planting::class)->withoutGlobalScope('owner');
    }
}
