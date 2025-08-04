<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('analysis_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->text('question'); // Pregunta o tipo de análisis
            $table->text('answer'); // Respuesta de la IA
            $table->string('model_used')->nullable(); // Ej: gpt-4o-mini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_logs');
    }
};
