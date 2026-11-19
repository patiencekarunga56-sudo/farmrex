<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Data isolation between farmers.
 *
 * A signed-in farmer only ever sees rows whose user_id is their own.
 * Administrators (and console commands such as the seeder, where nobody is
 * signed in) are not restricted, so the admin sees every farmer's records.
 */
trait OwnedRecords
{
    public const OWNER_SCOPE = 'owner';

    protected static function bootOwnedRecords(): void
    {
        static::addGlobalScope(self::OWNER_SCOPE, function (Builder $query) {
            $user = auth()->user();

            if ($user && ! $user->isAdmin()) {
                $query->where($query->getModel()->getTable().'.user_id', $user->id);
            }
        });
    }

    /** The farmer who recorded this row. */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
