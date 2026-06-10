<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pincodes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->unique();
            $table->boolean('available')->default(true);
            $table->timestamps();

            $table->index('available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pincodes');
    }
};
