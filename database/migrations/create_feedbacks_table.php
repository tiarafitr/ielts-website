<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attempt_id')->unique()->constrained()->cascadeOnDelete();

            $table->decimal('band_score', 3, 1);

            $table->jsonb('criteria')->nullable();
            $table->jsonb('strengths');
            $table->jsonb('areas_to_improve');

            $table->jsonb('raw_response')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
