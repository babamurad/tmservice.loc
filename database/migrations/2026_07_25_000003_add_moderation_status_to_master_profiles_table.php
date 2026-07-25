<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_profiles', function (Blueprint $table) {
            $table->string('moderation_status')->default('pending')->after('is_free');
        });
    }

    public function down(): void
    {
        Schema::table('master_profiles', function (Blueprint $table) {
            $table->dropColumn('moderation_status');
        });
    }
};
