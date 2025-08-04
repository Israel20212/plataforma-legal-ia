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

    protected $document;
    protected $model;
    protected $question;

    /**
     * Create a new job instance.
     */
    public function __construct(Document $document, string $model, string $question)
    {
        $this->document = $document;
        $this->model = $model;
        $this->question = $question;
    }

    /**
     * Execute the job.
     */
    public function handle(OpenAIService $openAIService): void
    {
        Log::info('Iniciando job de análisis para el documento ' . $this->document->id);
        try {
            $pdfPath = storage_path("app/public/" . $this->document->archivo);
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
                $prompt = "A partir del siguiente texto, realiza tres tareas y estructura tu respuesta exactamente como se indica a continuación:

1.  **Análisis Detallado**: Realiza un análisis detallado del documento, identificando sus partes clave, obligaciones y derechos de forma concisa.
2.  **Resumen**: Genera un resumen de los puntos clave en menos de 300 palabras.
3.  **Extracción de Entidades**: Extrae las entidades nombradas (personas, organizaciones, lugares, fechas, valores monetarios) y devuélvelas como un array JSON de objetos con claves 'entity' y 'type'.

Texto del Documento:
---
{$text}
---

RESPUESTA ESTRUCTURADA:
###ANALYSIS###
[Aquí tu análisis detallado]
###SUMMARY###
[Aquí tu resumen]
###ENTITIES###
[Aquí tu array JSON de entidades]";

                Log::info('Enviando prompt unificado a OpenAI...');
                $response = $openAIService->callOpenAIAPI($prompt, $this->model, 2000, 0.5, false); // No usar json_mode aquí
                $fullResponse = $response['choices'][0]['message']['content'] ?? '';
                Log::info('Respuesta unificada de OpenAI recibida.');

                // Parsear la respuesta estructurada
                $analysis = $this->extractSection($fullResponse, 'ANALYSIS');
                $summary = $this->extractSection($fullResponse, 'SUMMARY');
                $entities = $this->extractSection($fullResponse, 'ENTITIES');

                // Guardar el log de análisis
                $this->document->analysisLogs()->create([
                    'question' => $this->question,
                    'answer' => $analysis,
                    'model_used' => $this->model,
                ]);
                Log::info('Registro de análisis guardado.');

                // Actualizar el documento
                $this->document->summary = $summary;
                $this->document->extracted_entities = $entities;
                $this->document->save();
                Log::info('Documento actualizado con resumen y entidades.');

            } else {
                // Mantener la lógica para preguntas específicas
                $prompt = "Basado en el documento, responde la siguiente pregunta de forma concisa: '{$this->question}'.\n\nTexto del Documento:\n---\n{$text}";
                Log::info('Enviando prompt a OpenAI para pregunta específica...');
                $response = $openAIService->callOpenAIAPI($prompt, $this->model, 1500, 0.5, true);
                $answer = $response['choices'][0]['message']['content'] ?? 'Hubo un error al procesar la solicitud.';
                Log::info('Respuesta de OpenAI recibida.');

                $this->document->analysisLogs()->create([
                    'question' => $this->question,
                    'answer' => $answer,
                    'model_used' => $this->model,
                ]);
                Log::info('Registro de análisis guardado en la base de datos.');
            }

            Log::info('Job de análisis completado exitosamente para el documento ' . $this->document->id);

        } catch (\Exception $e) {
            Log::error('Error procesando el job de análisis para el documento ' . $this->document->id . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
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
        
        // Find the start of the next section to determine the end of the current one
        preg_match($endTagPattern, $text, $matches, PREG_OFFSET_CAPTURE, $startPos);
        
        $content = '';
        if (!empty($matches)) {
            $endPos = $matches[0][1];
            $content = substr($text, $startPos, $endPos - $startPos);
        } else {
            // If no next section is found, take the rest of the string
            $content = substr($text, $startPos);
        }

        // Clean up the content, especially for JSON
        $content = trim($content);
        if (str_starts_with($content, '```json')) {
            $content = str_replace('```json', '', $content);
            $content = rtrim($content, '`');
        }
        $content = trim($content);

        return $content;
    }
}
