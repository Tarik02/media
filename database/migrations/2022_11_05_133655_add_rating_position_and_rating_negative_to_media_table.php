<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->bigInteger('rating_positive')->default(0)->after('rating');

            $table->bigInteger('rating_negative')->default(0)->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('rating_positive');

            $table->dropColumn('rating_negative');
        });
    }
};
