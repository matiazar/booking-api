<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per open range, so split shifts (09-13 / 15-19) are just two rows.
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // ISO-8601: 1 = Monday ... 7 = Sunday
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();

            $table->index(['professional_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
