<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = DB::table('mail_templates')->where('slug', 'rsvp_decision_guest_approved')->first();
        if ($row === null) {
            return;
        }

        $html = $row->body_html;
        $text = $row->body_text;
        $desc = $row->description;

        if ($html !== null && ! str_contains((string) $html, '{{calendar_links_section}}')) {
            $html = str_replace(
                '{{table_number_section}}',
                "{{table_number_section}}\n    {{calendar_links_section}}",
                (string) $html
            );
        }

        if ($text !== null && ! str_contains((string) $text, '{{calendar_links_text}}')) {
            $text = str_replace(
                '{{table_number_text}}',
                "{{table_number_text}}\n{{calendar_links_text}}\n",
                (string) $text
            );
        }

        $newDesc = 'Placeholders: {{guest_name}}, {{guests_count}}, {{attendance_summary}}, {{table_number_section}}, {{table_number_text}}, {{calendar_links_section}} (HTML when calendar enabled in Settings), {{calendar_links_text}} (plain URLs)';

        DB::table('mail_templates')->where('id', $row->id)->update([
            'body_html' => $html,
            'body_text' => $text,
            'description' => $newDesc,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Non-reversible content merge; admins can reset template from seed if needed.
    }
};
