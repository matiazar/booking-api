<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            // ULID: public, unguessable id that still sorts by creation time.
            $table->ulid('id')->primary();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_type_id')->constrained()->restrictOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->dateTime('starts_at'); // stored in UTC
            $table->dateTime('ends_at');   // stored in UTC
            $table->string('status', 20);
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['professional_id', 'status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
