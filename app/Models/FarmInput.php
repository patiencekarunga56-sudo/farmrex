<?php

namespace App\Models;

use App\Models\Concerns\OwnedRecords;
use Illuminate\Database\Eloquent\Model;

class FarmInput extends Model
{
    use OwnedRecords;

    protected $guarded = ['id'];

    protected $casts = ['purchase_date' => 'date'];

    public function planting()
    {
        return $this->belongsTo(Planting::class)->withoutGlobalScope('owner');
    }
}
