<?php

namespace App\Models;

use App\Models\Concerns\OwnedRecords;
use Illuminate\Database\Eloquent\Model;

class Planting extends Model
{
    use OwnedRecords;

    public const STAGES = [
        'Land preparation', 'Planted', 'Germination', 'Vegetative',
        'Flowering', 'Fruiting', 'Ready for harvest', 'Harvested',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'planting_date' => 'date',
        'expected_harvest_date' => 'date',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class)->withoutGlobalScope('owner');
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class)->withoutGlobalScope('owner');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function observations()
    {
        return $this->hasMany(Observation::class);
    }

    public function harvests()
    {
        return $this->hasMany(Harvest::class);
    }

    /** e.g. "Maize – Field A (Sep 2026)" */
    public function getLabelAttribute(): string
    {
        return ($this->crop->name ?? 'Crop').' – '.($this->farm->name ?? 'Field')
            .' ('.$this->planting_date->format('M Y').')';
    }

    /** 0-100 progress along the growth stages. */
    public function getProgressAttribute(): int
    {
        $i = array_search($this->growth_stage, self::STAGES, true);
        $i = $i === false ? 0 : $i;

        return (int) round($i / (count(self::STAGES) - 1) * 100);
    }
}
