<?php

use App\Mail\MailTemplateSeed;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        foreach (MailTemplateSeed::all() as $row) {
            DB::table('mail_templates')->insert([
                'slug' => $row['slug'],
                'name' => $row['name'],
                'description' => $row['description'],
                'subject' => $row['subject'],
                'body_html' => $row['body_html'],
                'body_text' => $row['body_text'],
                'sort_order' => $row['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
