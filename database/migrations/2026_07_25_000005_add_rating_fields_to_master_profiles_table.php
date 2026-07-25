<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_profiles', function (Blueprint $table) {
            $table->decimal('avg_rating', 3, 2)->default(0)->after('moderation_status');
            $table->unsignedInteger('reviews_count')->default(0)->after('avg_rating');
        });
    }

    public function down(): void
    {
        Schema::table('master_profiles', function (Blueprint $table) {
            $table->dropColumn(['avg_rating', 'reviews_count']);
        });
    }
};
