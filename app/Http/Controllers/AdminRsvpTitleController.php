<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRsvpTitleController extends Controller
{
    public function edit(): View
    {
        $title = Setting::get('rsvp_page_title', 'Wedding RSVP');
        $subtitle = Setting::get('rsvp_page_subtitle', 'We would love to hear from you.');

        return view('admin.rsvp-title.edit', compact('title', 'subtitle'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::set('rsvp_page_title', $validated['title']);
        Setting::set('rsvp_page_subtitle', $validated['subtitle'] ?? '');

        return redirect()
            ->route('admin.rsvp-title.edit')
            ->with('success', 'RSVP title saved.');
    }
}
