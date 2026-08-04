<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('master_profile_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 500);
            // pending/resolved/dismissed — admin разбирает жалобу, но это НЕ
            // меняет master_profiles.moderation_status автоматически: одна
            // жалоба не должна сама по себе скрывать мастера, решение
            // принимает админ отдельно через уже существующую модерацию.
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
