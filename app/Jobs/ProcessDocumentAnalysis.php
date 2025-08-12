<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDocumentAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 1; // El job no se reintentará si falla

    // Usar la promoción de propiedades del constructor de PHP 8
    public function __construct(
        public int $documentId,
        public string $model,
        public string $question
    ) {}

    public function handle(OpenAIService $ai): void
    {
        Log::info('IA: Job de análisis iniciado.', ['doc_id' => $this->documentId]);

        $document = Document::findOrFail($this->documentId);

        try {
            // La lógica compleja se delega completamente al servicio
            [$summary, $entities, $analysis] = $ai->analyzeDocument($document, $this->model, $this->question);

            // Guardar el log de análisis general
            $document->analysisLogs()->create([
                'question' => 'Análisis General del Documento',
                'answer'   => $analysis,
                'model_used' => $this->model,
            ]);

            // Actualizar el documento con todos los resultados
            $document->update([
                'summary'           => $summary,
                'extracted_entities' => $entities, // El servicio ya debería devolver un JSON string
                'analysis_complete' => true,
            ]);

            Log::info('IA: Job de análisis completado exitosamente.', ['doc_id' => $this->documentId]);

        } catch (\Throwable $e) {
            // Si el servicio lanza una excepción, el job fallará.
            // Marcar el análisis como no completado para evitar estados inconsistentes.
            $document->update(['analysis_complete' => false]);
            Log::error('IA: Job de análisis falló.', [
                'doc_id' => $this->documentId,
                'error' => $e->getMessage()
            ]);
            // Re-lanzar la excepción para que Laravel mueva el job a la tabla `failed_jobs`
            $this->fail($e);
        }
    }
}
