<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncUpdate extends Model
{
    protected $table = 'sync_updates';

    protected $fillable = [
        'entity',
        'version',
    ];

    /** Increment version for an entity (e.g. Product), or insert row at 1 if missing. */
    public static function bumpEntity(string $entity): void
    {
        $updated = self::query()
            ->where('entity', $entity)
            ->increment('version');

        if ($updated === 0) {
            self::create([
                'entity' => $entity,
                'version' => 1,
            ]);
        }
    }
}


