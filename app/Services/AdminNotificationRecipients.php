<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Str;

class AdminNotificationRecipients
{
    /**
     * @return list<string>
     */
    public static function notificationEmails(): array
    {
        $emails = User::query()
            ->where('is_admin', true)
            ->pluck('email')
            ->map(fn (string $e) => Str::lower(trim($e)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $extra = Str::lower(trim(Setting::get('admin_notification_email')));
        if ($extra !== '' && ! in_array($extra, $emails, true)) {
            $emails[] = $extra;
        }

        return $emails;
    }

    /**
     * Deduped international digit strings (no +), for SMS.
     *
     * @return list<string>
     */
    public static function normalizedAdminSmsPhones(): array
    {
        $cc = Setting::get('sms_country_code', '233');
        $seen = [];
        $out = [];

        foreach (User::query()->where('is_admin', true)->cursor() as $user) {
            $raw = trim((string) ($user->phone ?? ''));
            if ($raw === '') {
                continue;
            }
            $n = ArkeselSmsService::normalizeRecipient($raw, $cc);
            if ($n !== null && ! isset($seen[$n])) {
                $seen[$n] = true;
                $out[] = $n;
            }
        }

        $extra = trim(Setting::get('admin_notification_phone'));
        if ($extra !== '') {
            $n = ArkeselSmsService::normalizeRecipient($extra, $cc);
            if ($n !== null && ! isset($seen[$n])) {
                $seen[$n] = true;
                $out[] = $n;
            }
        }

        return $out;
    }
}
