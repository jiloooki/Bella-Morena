<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_episodes', function (Blueprint $table) {
            if (!Schema::hasColumn('post_episodes', 'air_date')) {
                $table->date('air_date')->nullable()->after('runtime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('post_episodes', function (Blueprint $table) {
            if (Schema::hasColumn('post_episodes', 'air_date')) {
                $table->dropColumn('air_date');
            }
        });
    }
};
