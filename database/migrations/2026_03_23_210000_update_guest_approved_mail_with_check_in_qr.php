<?php

use App\Mail\MailTemplateSeed;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = MailTemplateSeed::forSlug('rsvp_decision_guest_approved');
        if ($row === null) {
            return;
        }

        DB::table('mail_templates')->where('slug', 'rsvp_decision_guest_approved')->update([
            'description' => $row['description'],
            'body_html' => $row['body_html'],
            'body_text' => $row['body_text'],
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        //
    }
};
