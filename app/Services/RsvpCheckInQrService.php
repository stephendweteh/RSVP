<?php

namespace App\Services;

use App\Models\Rsvp;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Mail\Message;
use Throwable;

class RsvpCheckInQrService
{
    /**
     * Placeholder replaced at send time with a CID URL via {@see Message::embedData()}.
     * Data URIs are stripped by many email clients (Gmail, Outlook, etc.).
     */
    public const EMAIL_QR_IMG_PLACEHOLDER = '__RSVP_CHECK_IN_QR_CID__';

    /**
     * Full URL encoded in the QR (guests can open it; staff scanner also accepts this string).
     */
    public static function qrPayload(Rsvp $rsvp): ?string
    {
        $token = $rsvp->check_in_token;
        if (! filled($token) || $rsvp->status !== Rsvp::STATUS_APPROVED) {
            return null;
        }

        return route('rsvp.admission.show', ['token' => $token], true);
    }

    /**
     * @return array{section_html: string, png: ?string}
     */
    public static function checkInEmailData(Rsvp $rsvp): array
    {
        $payload = self::qrPayload($rsvp);
        if ($payload === null) {
            return ['section_html' => '', 'png' => null];
        }

        try {
            $result = (new Builder)->build(
                data: $payload,
                size: 220,
                margin: 8,
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                encoding: new Encoding('UTF-8'),
            );
            $png = $result->getString();
        } catch (Throwable) {
            return ['section_html' => '', 'png' => null];
        }

        $sectionHtml = '<div style="text-align:center;margin:20px 0;padding:16px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0;">'
            .'<p style="margin:0 0 8px;font-weight:600;">Event check-in</p>'
            .'<p style="margin:0 0 12px;color:#64748b;font-size:14px;">Show this QR code at the entrance. You can also open the link on your phone.</p>'
            .'<img src="'.self::EMAIL_QR_IMG_PLACEHOLDER.'" width="220" height="220" alt="Check-in QR code" style="display:inline-block;border-radius:8px;" />'
            .'</div>';

        return ['section_html' => $sectionHtml, 'png' => $png];
    }

    /**
     * HTML block for the approval email, or empty string.
     */
    public static function emailSectionHtml(Rsvp $rsvp): string
    {
        return self::checkInEmailData($rsvp)['section_html'];
    }

    /**
     * Plain-text lines for the approval email.
     */
    public static function emailSectionText(Rsvp $rsvp): string
    {
        $payload = self::qrPayload($rsvp);
        if ($payload === null) {
            return '';
        }

        return "\n\nCheck-in link (show QR from your approval email or open):\n".$payload."\n";
    }

    /**
     * Extract token from raw scanner output (URL or bare token).
     */
    public static function normalizeTokenFromScan(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if (preg_match('#/rsvp/admission/([A-Za-z0-9]+)#', $raw, $m)) {
            return $m[1];
        }

        return $raw;
    }
}
