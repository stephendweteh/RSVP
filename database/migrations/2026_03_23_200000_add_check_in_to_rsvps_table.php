<?php

use App\Models\Rsvp;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            $table->string('check_in_token', 64)->nullable()->unique()->after('table_number');
            $table->timestamp('checked_in_at')->nullable()->after('check_in_token');
        });

        foreach (Rsvp::query()->where('status', Rsvp::STATUS_APPROVED)->whereNull('check_in_token')->cursor() as $rsvp) {
            $rsvp->forceFill(['check_in_token' => Str::random(40)])->save();
        }
    }

    public function down(): void
    {
        Schema::table('rsvps', function (Blueprint $table) {
            $table->dropColumn(['check_in_token', 'checked_in_at']);
        });
    }
};
