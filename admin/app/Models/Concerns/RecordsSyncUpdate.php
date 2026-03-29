<?php

namespace App\Models\Concerns;

use App\Models\SyncUpdate;

trait RecordsSyncUpdate
{
    protected static function bootRecordsSyncUpdate()
    {
        static::saved(function ($model) {
            static::recordSyncUpdate();
        });

        static::deleted(function ($model) {
            static::recordSyncUpdate();
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                static::recordSyncUpdate();
            });
        }
    }

    protected static function recordSyncUpdate(): void
    {
        SyncUpdate::bumpEntity(class_basename(static::class));
    }
}


