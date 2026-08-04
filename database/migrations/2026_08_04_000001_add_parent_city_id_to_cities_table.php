<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            // Посёлки-спутники Туркменабада (см. plan/README.md, "Города и
            // посёлки-спутники Туркменабада") — без отдельной модели District,
            // самоссылка на "головной" город. null = сам головной город.
            $table->foreignId('parent_city_id')->nullable()->after('id')->constrained('cities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_city_id');
        });
    }
};
