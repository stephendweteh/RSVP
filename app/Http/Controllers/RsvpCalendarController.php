<?php

namespace App\Http\Controllers;

use App\Services\EventCalendarService;
use Symfony\Component\HttpFoundation\Response;

class RsvpCalendarController extends Controller
{
    public function __invoke(): Response
    {
        $body = EventCalendarService::icsFileBody();
        if ($body === null) {
            abort(404, 'Calendar event is not configured.');
        }

        $filename = 'event-'.now()->format('Y-m-d').'.ics';

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
