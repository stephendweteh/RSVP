<?php

use App\Mail\MailTemplateSeed;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = MailTemplateSeed::forSlug('rsvp_not_attending_guest');
        if ($row === null) {
            return;
        }

        DB::table('mail_templates')->updateOrInsert(
            ['slug' => $row['slug']],
            [
                'name' => $row['name'],
                'description' => $row['description'],
                'subject' => $row['subject'],
                'body_html' => $row['body_html'],
                'body_text' => $row['body_text'],
                'sort_order' => $row['sort_order'],
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        DB::table('mail_templates')->where('slug', 'rsvp_not_attending_guest')->delete();
    }
};
