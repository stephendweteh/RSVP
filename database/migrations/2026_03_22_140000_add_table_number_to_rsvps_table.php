<?php

use App\Models\Rsvp;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rsvps', function (Blueprint $table): void {
            $table->unsignedTinyInteger('table_number')->nullable()->after('status');
        });

        $n = 1;
        foreach (DB::table('rsvps')->where('status', Rsvp::STATUS_APPROVED)->orderBy('id')->cursor() as $row) {
            if ($n > Rsvp::APPROVED_CAPACITY) {
                break;
            }
            DB::table('rsvps')->where('id', $row->id)->update(['table_number' => $n]);
            $n++;
        }
    }

    public function down(): void
    {
        Schema::table('rsvps', function (Blueprint $table): void {
            $table->dropColumn('table_number');
        });
    }
};
