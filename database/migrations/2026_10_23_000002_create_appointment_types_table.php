<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedInteger('price_cents')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_types');
    }
};
