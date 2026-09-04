<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('part');

            $table->string('topic');
            $table->text('prompt');
            $table->timestamps();

            $table->index(['part', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
