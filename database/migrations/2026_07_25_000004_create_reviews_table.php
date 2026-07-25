<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('master_profile_id')->constrained('master_profiles')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->string('moderation_status')->default('pending');
            $table->timestamps();

            $table->unique(['client_id', 'master_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
