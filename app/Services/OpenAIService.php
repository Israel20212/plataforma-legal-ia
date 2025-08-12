<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $apiKey;
    protected $model;
    protected $maxTokens;
    protected $chunkSize;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
        $this->model = config('openai.model', 'gpt-4');
        $this->maxTokens = config('openai.max_tokens', 1500);
        $this->chunkSize = config('openai.chunk_size', 2000); // Tamaño del chunk en caracteres
    }

    public function callOpenAIAPI(string $prompt, ?string $model = null, ?int $maxTokens = null, float $temperature = 0.5, bool $isChunkable = false)
    {
        $model = $model ?? $this->model;
        $maxTokens = $maxTokens ?? $this->maxTokens;

        // Si el prompt es largo y se marca como chunkable, lo dividimos
        if ($isChunkable && strlen($prompt) > $this->chunkSize) {
            $chunks = $this->splitTextIntoChunks($prompt);
            $responses = [];

            foreach ($chunks as $chunk) {
                $response = $this->makeApiCall($chunk, $model, $maxTokens, $temperature);
                if (isset($response['choices'][0]['message']['content'])) {
                    $responses[] = $response['choices'][0]['message']['content'];
                }
            }
            // Para este ejemplo, devolvemos el contenido combinado. 
            // En un caso real, podríamos necesitar una llamada final para unificar.
            return ['choices' => [['message' => ['content' => implode("\n\n", $responses)]]]];
        }

        // Si no, hacemos una sola llamada
        return $this->makeApiCall($prompt, $model, $maxTokens, $temperature);
    }

    private function makeApiCall(string $prompt, string $model, int $maxTokens, float $temperature)
    {
        Log::info("Realizando llamada a la API de OpenAI", ['model' => $model]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(180)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful legal assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ]);

        if ($response->failed()) {
            Log::error("Error en la solicitud HTTP a OpenAI", [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            $response->throw();
        }

        $jsonResponse = $response->json();

        if (isset($jsonResponse['error'])) {
            Log::error("Error devuelto por la API de OpenAI", [
                'error' => $jsonResponse['error']
            ]);
            throw new \Exception("OpenAI API Error: " . $jsonResponse['error']['message']);
        }

        Log::info("Llamada a la API de OpenAI exitosa");
        return $jsonResponse;
    }

    private function splitTextIntoChunks(string $text): array
    {
        $chunks = [];
        $length = strlen($text);
        for ($i = 0; $i < $length; $i += $this->chunkSize) {
            $chunks[] = substr($text, $i, $this->chunkSize);
        }
        return $chunks;
    }
}