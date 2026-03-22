<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public static function get(string $key, string $default = ''): string
    {
        $row = static::query()->where('key', $key)->first();

        if ($row === null) {
            return $default;
        }

        return (string) ($row->value ?? '');
    }

    public static function set(string $key, string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    public static function getDecrypted(string $key, string $default = ''): string
    {
        $stored = static::get($key);
        if ($stored === '') {
            return $default;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (Throwable) {
            return $default;
        }
    }

    public static function setEncrypted(string $key, string $plain): void
    {
        static::set($key, Crypt::encryptString($plain));
    }
}
