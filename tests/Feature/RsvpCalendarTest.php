<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RsvpCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_ics_returns_404_when_calendar_not_configured(): void
    {
        $this->get(route('rsvp.calendar.ics'))->assertNotFound();
    }

    public function test_ics_download_when_calendar_configured(): void
    {
        Setting::set('calendar_event_enabled', '1');
        Setting::set('calendar_event_title', 'Wedding');
        Setting::set('calendar_event_start', '2030-06-01T16:00');
        Setting::set('calendar_event_end', '2030-06-01T23:00');
        Setting::set('calendar_event_location', 'Garden Venue');
        Setting::set('calendar_event_description', 'Dress code: formal');

        $response = $this->get(route('rsvp.calendar.ics'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('SUMMARY:Wedding', $response->getContent());
        $this->assertStringContainsString('LOCATION:Garden Venue', $response->getContent());
    }

    public function test_admin_can_save_calendar_settings(): void
    {
        $user = User::factory()->administrator()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.calendar.update'), [
                'calendar_event_enabled' => '1',
                'calendar_event_title' => 'Big Day',
                'calendar_event_start' => '2030-07-04T18:00',
                'calendar_event_end' => '2030-07-05T01:00',
                'calendar_event_location' => 'Hall',
                'calendar_event_description' => '',
            ])
            ->assertRedirect(route('admin.settings.edit').'#calendar-event');

        $this->assertSame('1', Setting::get('calendar_event_enabled'));
        $this->assertSame('Big Day', Setting::get('calendar_event_title'));
    }
}
