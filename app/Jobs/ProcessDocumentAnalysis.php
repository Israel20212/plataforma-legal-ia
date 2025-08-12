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
use Smalot\PdfParser\Parser;

class ProcessDocumentAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    protected $documentId;
    protected $model;
    protected $question;

    /**
     * Create a new job instance.
     */
    public function __construct(int $documentId, string $model, string $question)
    {
        $this->documentId = $documentId;
        $this->model = $model;
        $this->question = $question;
    }

    /**
     * Execute the job.
     */
    public function handle(OpenAIService $openAIService): void
    {
        // Cargar el documento usando el ID
        $document = Document::findOrFail($this->documentId);
        Log::info('Iniciando job de análisis para el documento ' . $document->id);

        try {
            $pdfPath = storage_path("app/public/" . $document->archivo);
            Log::info('Ruta del PDF: ' . $pdfPath);
            if (!file_exists($pdfPath)) {
                Log::error('Archivo no encontrado en la ruta: ' . $pdfPath);
                throw new \Exception("File not found at path: {$pdfPath}");
            }

            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();
            Log::info('PDF parseado correctamente. Longitud del texto: ' . strlen($text) . ' caracteres.');

            if ($this->question === 'Análisis General del Documento') {
                // Unificar las 3 llamadas en 1 para el análisis general
                $prompt = "A partir del siguiente texto, realiza tres tareas y estructura tu respuesta exactamente como se indica a continuación:\n\n1.  **Análisis Detallado**: Realiza un análisis detallado del documento, identificando sus partes clave, obligaciones y derechos de forma concisa.\n2.  **Resumen**: Genera un resumen de los puntos clave en menos de 300 palabras.\n3.  **Extracción de Entidades**: Extrae las entidades nombradas (personas, organizaciones, lugares, fechas, valores monetarios) y devuélvelas como un array JSON de objetos con claves 'entity' y 'type'.\n\nTexto del Documento:\n---\n{$text}\n---\n\nRESPUESTA ESTRUCTURADA:\n###ANALYSIS###\n[Aquí tu análisis detallado]\n###SUMMARY###\n[Aquí tu resumen]\n###ENTITIES###\n[Aquí tu array JSON de entidades]";

                Log::info('Enviando prompt unificado a OpenAI...');
                $response = $openAIService->callOpenAIAPI($prompt, $this->model, 2000, 0.5, false);
                $fullResponse = $response['choices'][0]['message']['content'] ?? '';
                Log::info('Respuesta unificada de OpenAI recibida.');

                $analysis = $this->extractSection($fullResponse, 'ANALYSIS');
                $summary = $this->extractSection($fullResponse, 'SUMMARY');
                $entitiesRaw = $this->extractSection($fullResponse, 'ENTITIES');

                $entities = null;
                if (!empty($entitiesRaw)) {
                    try {
                        $decoded = json_decode($entitiesRaw, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $entities = $entitiesRaw;
                        } else {
                            Log::warning('El JSON de entidades extraídas no es válido para el documento ' . $document->id, ['raw_entities' => $entitiesRaw]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Excepción al decodificar el JSON de entidades para el documento ' . $document->id, ['error' => $e->getMessage()]);
                    }
                }

                // **CORRECCIÓN AQUÍ:** Usar la variable local `$document`
                $document->analysisLogs()->create([
                    'question' => $this->question,
                    'answer' => $analysis,
                    'model_used' => $this->model,
                ]);
                Log::info('Registro de análisis guardado.');

                // Actualizar el documento
                $document->update([
                    'summary' => $summary,
                    'extracted_entities' => $entities,
                    'analysis_complete' => true,
                ]);
                Log::info('Documento actualizado con resumen, entidades y estado completado.');

            } else {
                // Lógica para preguntas específicas
                $prompt = "Basado en el documento, responde la siguiente pregunta de forma concisa: '{$this->question}'.\n\nTexto del Documento:\n---\n{$text}";
                Log::info('Enviando prompt a OpenAI para pregunta específica...');
                $response = $openAIService->callOpenAIAPI($prompt, $this->model, 1500, 0.5, true);
                $answer = $response['choices'][0]['message']['content'] ?? 'Hubo un error al procesar la solicitud.';
                Log::info('Respuesta de OpenAI recibida.');

                // **CORRECCIÓN AQUÍ:** Usar la variable local `$document`
                $document->analysisLogs()->create([
                    'question' => $this->question,
                    'answer' => $answer,
                    'model_used' => $this->model,
                ]);
                Log::info('Registro de análisis guardado en la base de datos.');
            }

            Log::info('Job de análisis completado exitosamente para el documento ' . $document->id);

        } catch (\Exception $e) {
            // En caso de error, marcar como no completado
            $document->update(['analysis_complete' => false]);
            Log::error('Error procesando el job de análisis para el documento ' . $document->id . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->fail($e);
        }
    }

    private function extractSection(string $text, string $section): string
    {
        $startTag = "###{$section}###";
        $endTagPattern = '/###[A-Z]+###/';
        
        $startPos = strpos($text, $startTag);
        if ($startPos === false) {
            return '';
        }
        
        $startPos += strlen($startTag);
        
        preg_match($endTagPattern, $text, $matches, PREG_OFFSET_CAPTURE, $startPos);
        
        $content = '';
        if (!empty($matches)) {
            $endPos = $matches[0][1];
            $content = substr($text, $startPos, $endPos - $startPos);
        } else {
            $content = substr($text, $startPos);
        }

        $content = trim($content);
        if (preg_match('/^```json\s*(.*?)\s*```$/s', $content, $jsonMatches)) {
            $content = $jsonMatches[1];
        }
        $content = trim($content);

        return $content;
    }
}
