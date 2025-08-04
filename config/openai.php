<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Model Configuration
    |--------------------------------------------------------------------------
    |
    | Este archivo almacena las credenciales para los servicios de OpenAI.
    | Deberías establecer tu clave de API de OpenAI en tu archivo .env.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),

    'models' => [
        'gpt-4o' => 'GPT-4o (Recomendado)',
        'gpt-4o-mini' => 'GPT-4o Mini (Más rápido y económico)',
        'gpt-4-turbo' => 'GPT-4 Turbo',
    ],

    'max_tokens' => env('OPENAI_MAX_TOKENS', 4096),

    // Recuento de caracteres para dividir el texto, dejando un búfer para el prompt y los tokens.
    'chunk_size' => env('OPENAI_CHUNK_SIZE', 3500),
];