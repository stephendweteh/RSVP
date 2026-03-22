<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class DatabaseMailConfig
{
    public static function applyIfConfigured(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $host = trim(Setting::get('mail_smtp_host'));
        if ($host === '') {
            return;
        }

        $port = (int) (Setting::get('mail_smtp_port', '587') ?: 587);
        $encryption = Setting::get('mail_smtp_encryption', 'tls');
        $scheme = match ($encryption) {
            'ssl' => 'smtps',
            default => 'smtp',
        };

        $password = Setting::getDecrypted('mail_smtp_password');
        $fromAddress = trim(Setting::get('mail_from_address'));
        $fromName = Setting::get('mail_from_name', config('mail.from.name'));

        Config::set([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => Setting::get('mail_smtp_username'),
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.scheme' => $scheme,
        ]);

        if ($fromAddress !== '') {
            Config::set('mail.from.address', $fromAddress);
        }
        if ($fromName !== '') {
            Config::set('mail.from.name', $fromName);
        }
    }
}
