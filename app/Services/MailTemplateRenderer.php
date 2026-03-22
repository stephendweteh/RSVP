<?php

namespace App\Services;

use App\Models\MailTemplate;
use App\Models\Rsvp;

class MailTemplateRenderer
{
    public static function interpolate(string $template, array $vars): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            static function (array $m) use ($vars): string {
                $key = $m[1];

                return (string) ($vars[$key] ?? '');
            },
            $template
        );
    }

    /**
     * @param  array<string, string>  $varsHtml
     * @param  array<string, string>  $varsText
     * @return array{subject: string, html: string, text: string}
     */
    public static function render(MailTemplate $mailTemplate, array $varsHtml, array $varsText): array
    {
        return [
            'subject' => self::interpolate($mailTemplate->subject, $varsText),
            'html' => self::interpolate($mailTemplate->body_html, $varsHtml),
            'text' => self::interpolate($mailTemplate->body_text, $varsText),
        ];
    }

    /**
     * @return array{html: array<string, string>, text: array<string, string>}
     */
    public static function varsForSubmittedGuest(Rsvp $rsvp): array
    {
        $att = $rsvp->attendance === 'attending' ? 'Attending' : 'Not attending';

        return [
            'html' => [
                'guest_name' => e($rsvp->name),
                'guests_count' => (string) $rsvp->guests_count,
                'attendance_label' => $att,
            ],
            'text' => [
                'guest_name' => $rsvp->name,
                'guests_count' => (string) $rsvp->guests_count,
                'attendance_label' => $att,
            ],
        ];
    }

    /**
     * @return array{html: array<string, string>, text: array<string, string>}
     */
    public static function varsForSubmittedAdmin(Rsvp $rsvp): array
    {
        $messageSection = '';
        $messageText = '';
        if (filled($rsvp->message)) {
            $messageSection = '<p><strong>Message</strong></p><p style="white-space: pre-wrap;">'.e($rsvp->message).'</p>';
            $messageText = "\n\nMessage:\n".$rsvp->message;
        }

        $att = $rsvp->attendance === 'attending' ? 'Attending' : 'Not attending';

        return [
            'html' => [
                'guest_name' => e($rsvp->name),
                'guest_email' => e((string) ($rsvp->email ?? '')),
                'guest_phone' => e($rsvp->phone),
                'guests_count' => (string) $rsvp->guests_count,
                'attendance_label' => $att,
                'guest_message_section' => $messageSection,
                'guest_message_text' => $messageText,
            ],
            'text' => [
                'guest_name' => $rsvp->name,
                'guest_email' => (string) ($rsvp->email ?? ''),
                'guest_phone' => $rsvp->phone,
                'guests_count' => (string) $rsvp->guests_count,
                'attendance_label' => $att,
                'guest_message_section' => '',
                'guest_message_text' => $messageText,
            ],
        ];
    }

    /**
     * @return array{html: array<string, string>, text: array<string, string>}
     */
    public static function varsForDecisionGuestApproved(Rsvp $rsvp): array
    {
        $tableSection = '';
        $tableText = '';
        if (filled($rsvp->table_number)) {
            $n = e((string) $rsvp->table_number);
            $tableSection = '<p>Your <strong>table number</strong> is <strong style="font-size:1.25em;">'.$n.'</strong>.</p>';
            $tableText = "\nYour table number: ".$rsvp->table_number;
        }

        $summary = $rsvp->guests_count.' guest(s), '.($rsvp->attendance === 'attending' ? 'attending' : 'not attending');

        $calendarHtml = EventCalendarService::calendarLinksSectionHtml();
        $calendarText = EventCalendarService::calendarLinksSectionText();

        $checkInHtml = RsvpCheckInQrService::emailSectionHtml($rsvp);
        $checkInText = RsvpCheckInQrService::emailSectionText($rsvp);

        return [
            'html' => [
                'guest_name' => e($rsvp->name),
                'guests_count' => (string) $rsvp->guests_count,
                'attendance_summary' => $summary,
                'table_number_section' => $tableSection,
                'table_number_text' => $tableText,
                'check_in_qr_section' => $checkInHtml,
                'calendar_links_section' => $calendarHtml,
            ],
            'text' => [
                'guest_name' => $rsvp->name,
                'guests_count' => (string) $rsvp->guests_count,
                'attendance_summary' => $summary,
                'table_number_section' => '',
                'table_number_text' => $tableText,
                'check_in_qr_section' => '',
                'check_in_qr_text' => $checkInText,
                'calendar_links_section' => '',
                'calendar_links_text' => $calendarText,
            ],
        ];
    }

    /**
     * @return array{html: array<string, string>, text: array<string, string>}
     */
    public static function varsForDecisionGuestRejected(Rsvp $rsvp): array
    {
        $summary = $rsvp->guests_count.' guest(s), '.($rsvp->attendance === 'attending' ? 'attending' : 'not attending');

        return [
            'html' => [
                'guest_name' => e($rsvp->name),
                'guests_count' => (string) $rsvp->guests_count,
                'attendance_summary' => $summary,
            ],
            'text' => [
                'guest_name' => $rsvp->name,
                'guests_count' => (string) $rsvp->guests_count,
                'attendance_summary' => $summary,
            ],
        ];
    }

    /**
     * @return array{html: array<string, string>, text: array<string, string>}
     */
    public static function varsForDecisionAdmin(Rsvp $rsvp, string $decision): array
    {
        $statusLabel = $decision === Rsvp::STATUS_APPROVED ? 'Approved' : 'Rejected';
        $tableRow = '';
        $tableText = '';
        if ($decision === Rsvp::STATUS_APPROVED && filled($rsvp->table_number)) {
            $tableRow = '<li><strong>Table number:</strong> '.e((string) $rsvp->table_number).'</li>';
            $tableText = "\nTable number: ".$rsvp->table_number;
        }

        return [
            'html' => [
                'guest_name' => e($rsvp->name),
                'guest_email' => e((string) ($rsvp->email ?? '')),
                'status_label' => $statusLabel,
                'table_row_section' => $tableRow,
                'table_number_text' => $tableText,
            ],
            'text' => [
                'guest_name' => $rsvp->name,
                'guest_email' => (string) ($rsvp->email ?? ''),
                'status_label' => $statusLabel,
                'table_row_section' => '',
                'table_number_text' => $tableText,
            ],
        ];
    }
}
