<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->string('unique_key')->index()->unique();

            $table->unsignedBigInteger('file_id');

            $table->bigInteger('rating')->default(0);

            $table->string('title');

            $table->string('type')->index();

            $table->string('source');

            $table->json('data');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
