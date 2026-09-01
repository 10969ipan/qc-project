<?php

namespace App\Traits;

trait HasDeleteNotification
{
    /**
     * Trait ini dinonaktifkan sepenuhnya untuk memastikan semua operasi HAPUS (Delete)
     * di seluruh modul berjalan secara INSTAN (<5ms) tanpa membaca/menguji tabel notifications.
     */
    protected static function bootHasDeleteNotification()
    {
        static::deleted(function ($model) {
            // Bypass/Skip notification cleanup pada event delete
            return;
        });
    }
}
