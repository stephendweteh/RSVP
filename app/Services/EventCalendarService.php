<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EventCalendarService
{
    public static function isConfigured(): bool
    {
        if (Setting::get('calendar_event_enabled') !== '1') {
            return false;
        }

        return filled(Setting::get('calendar_event_title'))
            && filled(Setting::get('calendar_event_start'))
            && filled(Setting::get('calendar_event_end'));
    }

    /**
     * @return array{start: Carbon, end: Carbon}|null
     */
    public static function rangeUtc(): ?array
    {
        if (! self::isConfigured()) {
            return null;
        }

        $tz = (string) config('app.timezone', 'UTC');

        try {
            $start = Carbon::parse(Setting::get('calendar_event_start'), $tz)->utc();
            $end = Carbon::parse(Setting::get('calendar_event_end'), $tz)->utc();
        } catch (\Throwable) {
            return null;
        }

        if ($end->lessThanOrEqualTo($start)) {
            return null;
        }

        return ['start' => $start, 'end' => $end];
    }

    public static function googleCalendarUrl(): ?string
    {
        $range = self::rangeUtc();
        if ($range === null) {
            return null;
        }

        $title = Setting::get('calendar_event_title');
        $details = Setting::get('calendar_event_description');
        $location = Setting::get('calendar_event_location');

        $fmt = 'Ymd\THis\Z';
        $dates = $range['start']->format($fmt).'/'.$range['end']->format($fmt);

        $query = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $dates,
            'details' => $details,
            'location' => $location,
        ], '', '&', PHP_QUERY_RFC3986);

        return 'https://calendar.google.com/calendar/render?'.$query;
    }

    public static function outlookCalendarUrl(): ?string
    {
        $range = self::rangeUtc();
        if ($range === null) {
            return null;
        }

        $title = Setting::get('calendar_event_title');
        $body = Setting::get('calendar_event_description');
        $location = Setting::get('calendar_event_location');

        $query = http_build_query([
            'path' => '/calendar/action/compose',
            'rru' => 'addevent',
            'subject' => $title,
            'startdt' => $range['start']->toIso8601String(),
            'enddt' => $range['end']->toIso8601String(),
            'body' => $body,
            'location' => $location,
        ], '', '&', PHP_QUERY_RFC3986);

        return 'https://outlook.live.com/calendar/0/deeplink/compose?'.$query;
    }

    public static function icsDownloadUrl(): string
    {
        return route('rsvp.calendar.ics', [], true);
    }

    public static function calendarLinksSectionHtml(): string
    {
        if (! self::isConfigured()) {
            return '';
        }

        $google = self::googleCalendarUrl();
        $outlook = self::outlookCalendarUrl();
        $ics = self::icsDownloadUrl();

        if ($google === null || $outlook === null) {
            return '';
        }

        return '<p style="margin-top:1.25em;"><strong>Add to calendar</strong></p>'
            .'<p style="margin:0.35em 0 0;">'
            .'<a href="'.e($google).'">Google Calendar</a>'
            .' · <a href="'.e($outlook).'">Outlook</a>'
            .' · <a href="'.e($ics).'">Apple / other (.ics)</a>'
            .'</p>';
    }

    public static function calendarLinksSectionText(): string
    {
        if (! self::isConfigured()) {
            return '';
        }

        $google = self::googleCalendarUrl();
        $outlook = self::outlookCalendarUrl();
        $ics = self::icsDownloadUrl();

        if ($google === null || $outlook === null) {
            return '';
        }

        return "\nAdd to calendar:\n"
            ."Google Calendar: {$google}\n"
            ."Outlook: {$outlook}\n"
            ."Download .ics: {$ics}\n";
    }

    public static function icsFileBody(): ?string
    {
        $range = self::rangeUtc();
        if ($range === null) {
            return null;
        }

        $title = self::icsEscape(Setting::get('calendar_event_title'));
        $description = self::icsEscape(Setting::get('calendar_event_description'));
        $location = self::icsEscape(Setting::get('calendar_event_location'));
        $uid = self::icsEscape('wedding-'.Str::slug(config('app.name')).'-'.substr(sha1((string) config('app.key')), 0, 16).'@rsvp');

        $dtStamp = Carbon::now('UTC')->format('Ymd\THis\Z');
        $dtStart = $range['start']->format('Ymd\THis\Z');
        $dtEnd = $range['end']->format('Ymd\THis\Z');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//RSVP App//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$dtStamp,
            'DTSTART:'.$dtStart,
            'DTEND:'.$dtEnd,
            'SUMMARY:'.$title,
        ];

        if ($location !== '') {
            $lines[] = 'LOCATION:'.$location;
        }
        if ($description !== '') {
            $lines[] = 'DESCRIPTION:'.$description;
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    private static function icsEscape(string $value): string
    {
        $value = str_replace(["\r\n", "\r", "\n"], '\n', $value);

        return str_replace(['\\', ';', ','], ['\\\\', '\\;', '\\,'], $value);
    }
}
