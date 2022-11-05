<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id');

            $table->string('voter');

            $table->tinyInteger('value');

            $table->timestamps();

            $table->unique(['media_id', 'voter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_likes');
    }
};
