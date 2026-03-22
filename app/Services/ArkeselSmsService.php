<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ArkeselSmsService
{
    public const API_URL = 'https://sms.arkesel.com/api/v2/sms/send';

    public static function isConfigured(): bool
    {
        if (Setting::get('sms_arkesel_enabled') !== '1') {
            return false;
        }

        $sender = trim(Setting::get('sms_arkesel_sender'));
        if ($sender === '') {
            return false;
        }

        return self::apiKey() !== '';
    }

    public static function apiKey(): string
    {
        return Setting::getDecrypted('sms_arkesel_api_key');
    }

    /**
     * Normalize to digits only, international format without + (e.g. 233XXXXXXXXX).
     */
    public static function normalizeRecipient(string $phone, string $countryCodeDigits): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }

        $cc = preg_replace('/\D+/', '', $countryCodeDigits) ?? '';
        if ($cc === '') {
            $cc = '233';
        }

        if (str_starts_with($digits, '0')) {
            $digits = $cc.substr($digits, 1);
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    public static function send(string $recipientDigits, string $message): array
    {
        if (! self::isConfigured()) {
            return ['ok' => false, 'message' => 'SMS not configured'];
        }

        $apiKey = self::apiKey();
        $sender = trim(Setting::get('sms_arkesel_sender'));
        $body = mb_substr($message, 0, 480);

        try {
            /** @var Response $response */
            $response = Http::timeout(20)
                ->acceptJson()
                ->withHeaders([
                    'api-key' => $apiKey,
                ])
                ->post(self::API_URL, [
                    'sender' => $sender,
                    'message' => $body,
                    'recipients' => [$recipientDigits],
                ]);
        } catch (Throwable $e) {
            Log::warning('Arkesel SMS request failed', [
                'error' => $e->getMessage(),
                'recipient' => $recipientDigits,
            ]);

            return ['ok' => false, 'message' => $e->getMessage()];
        }

        $json = $response->json();
        $ok = $response->successful()
            && is_array($json)
            && (($json['status'] ?? null) === 'success');

        if (! $ok) {
            Log::warning('Arkesel SMS rejected', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'ok' => false,
                'message' => is_array($json) ? (string) ($json['message'] ?? $response->body()) : $response->body(),
            ];
        }

        return ['ok' => true];
    }
}
