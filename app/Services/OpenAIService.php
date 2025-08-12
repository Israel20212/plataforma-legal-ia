<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // <-- AÑADIR ESTA LÍNEA
use Smalot\PdfParser\Parser;

class OpenAIService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
    }

    /**
     * Analiza un documento, extrayendo resumen, entidades y análisis detallado.
     * Lanza una excepción si cualquier paso falla.
     *
     * @return array [string $summary, string $entities, string $analysis]
     * @throws \Throwable
     */
    public function analyzeDocument(Document $document, string $model, string $question): array
    {
        try {
            // 1. Extraer texto del PDF
            $text = $this->extractTextFromPdf($document);

            // 2. Construir el prompt
            $prompt = $this->buildStructuredPrompt($text);

            // 3. Llamar a la API de OpenAI
            $fullResponse = $this->makeApiCall($prompt, $model);

            // 4. Extraer las secciones de la respuesta
            $analysis = $this->extractSection($fullResponse, 'ANALYSIS');
            $summary = $this->extractSection($fullResponse, 'SUMMARY');
            $entitiesRaw = $this->extractSection($fullResponse, 'ENTITIES');

            // 5. Validar y limpiar el JSON de entidades
            $entitiesJson = $this->validateAndCleanJson($entitiesRaw, $document->id);

            return [$summary, $entitiesJson, $analysis];

        } catch (\Throwable $e) {
            Log::error('Error en el proceso de análisis de OpenAI', [
                'doc_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Re-lanzar la excepción para que el Job que lo llamó falle
            throw $e;
        }
    }

    private function extractTextFromPdf(Document $document): string
    {
        if (!Storage::disk('documents')->exists($document->archivo)) {
            $absPath = Storage::disk('documents')->path($document->archivo);
            throw new \Exception("Archivo PDF no encontrado en la ruta: {$absPath}");
        }
        $pdfPath = Storage::disk('documents')->path($document->archivo);
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        return $pdf->getText();
    }

    private function buildStructuredPrompt(string $text): string
    {
        return "A partir del siguiente texto, realiza tres tareas y estructura tu respuesta exactamente como se indica a continuación:\n\n1.  **Análisis Detallado**: Realiza un análisis detallado del documento, identificando sus partes clave, obligaciones y derechos de forma concisa.\n2.  **Resumen**: Genera un resumen de los puntos clave en menos de 300 palabras.\n3.  **Extracción de Entidades**: Extrae las entidades nombradas (personas, organizaciones, lugares, fechas, valores monetarios) y devuélvelas como un array JSON de objetos con claves 'entity' y 'type'.\n\nTexto del Documento:\n---\n{$text}\n---\n\nRESPUESTA ESTRUCTURADA:\n###ANALYSIS###\n[Aquí tu análisis detallado]\n###SUMMARY###\n[Aquí tu resumen]\n###ENTITIES###\n[Aquí tu array JSON de entidades]";
    }

    private function makeApiCall(string $prompt, string $model): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(180)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente legal muy preciso.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 2000,
            'temperature' => 0.3,
        ]);

        if ($response->failed()) {
            // Esto captura errores de red, timeouts, 5xx, etc.
            $response->throw();
        }

        $jsonResponse = $response->json();

        if (isset($jsonResponse['error'])) {
            // Esto captura errores específicos de la API de OpenAI (p.ej. API key inválida)
            throw new \Exception("Error de la API de OpenAI: " . $jsonResponse['error']['message']);
        }

        return $jsonResponse['choices'][0]['message']['content'] ?? '';
    }

    private function extractSection(string $text, string $section): string
    {
        $startTag = "###{$section}###";
        $endTagPattern = '/###[A-Z]+###/';
        $startPos = strpos($text, $startTag);
        if ($startPos === false) return '';
        $startPos += strlen($startTag);
        preg_match($endTagPattern, $text, $matches, PREG_OFFSET_CAPTURE, $startPos);
        $content = !empty($matches) ? substr($text, $startPos, $matches[0][1] - $startPos) : substr($text, $startPos);
        return trim($content);
    }

    private function validateAndCleanJson(string $rawJson, int $docId): ?string
    {
        // Limpiar posible markdown
        if (preg_match('/^```json\s*(.*?)\s*```$/s', $rawJson, $jsonMatches)) {
            $rawJson = $jsonMatches[1];
        }
        $rawJson = trim($rawJson);

        if (empty($rawJson)) return null;

        $decoded = json_decode($rawJson, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $rawJson; // Devolver el string JSON válido
        }

        Log::warning('El JSON de entidades extraídas no es válido', ['doc_id' => $docId, 'raw_json' => $rawJson]);
        return null; // Devolver null si el JSON no es válido
    }
}