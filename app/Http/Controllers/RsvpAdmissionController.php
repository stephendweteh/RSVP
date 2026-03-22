<?php

namespace App\Http\Controllers;

use App\Models\Rsvp;
use Illuminate\View\View;

class RsvpAdmissionController extends Controller
{
    public function show(string $token): View
    {
        $rsvp = Rsvp::query()
            ->where('check_in_token', $token)
            ->where('status', Rsvp::STATUS_APPROVED)
            ->first();

        return view('rsvp.admission', [
            'rsvp' => $rsvp,
            'valid' => $rsvp !== null,
        ]);
    }
}
