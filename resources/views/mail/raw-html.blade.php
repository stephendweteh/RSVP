@php
    $html = $body_html;
    $qrPng = $check_in_qr_png ?? null;
    if (is_string($qrPng) && $qrPng !== '' && isset($message)) {
        $cid = $message->embedData($qrPng, 'check-in-qr.png', 'image/png');
        $html = str_replace(\App\Services\RsvpCheckInQrService::EMAIL_QR_IMG_PLACEHOLDER, $cid, $html);
    }
@endphp
{!! $html !!}
