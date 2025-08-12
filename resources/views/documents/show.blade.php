@extends('layouts.base')

@section('title', 'Detalle del Documento')

@section('content')
<h2 class="text-2xl font-bold text-primary font-serif mb-4">{{ $document->titulo }}</h2>

<div class="mb-4 text-gray-600 text-sm">
    <p><strong>Tipo:</strong> {{ $document->tipo_documento ?? 'No especificado' }}</p>
    <p><strong>Subido el:</strong> {{ $document->created_at->format('d/m/Y') }}</p>
</div>

<div class="mb-6">
    <p class="text-gray-700">{{ $document->descripcion }}</p>
</div>

<div class="mb-6">
    <a href="{{ route('documents.stream', $document->id) }}" target="_blank"
       class="inline-block bg-gray-200 text-gray-800 text-sm px-4 py-2 rounded hover:bg-gray-300 transition">
        Ver PDF completo
    </a>
</div>

<!-- 🔮 Análisis por IA -->
<div class="mt-10 border-t pt-6">
    <h3 class="text-xl font-semibold text-secondary mb-2">🔍 Análisis Inteligente</h3>

    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('status') }}</span>
        </div>
    @endif

    @if($document->summary && $document->extracted_entities)
        <div class="mt-4">
            <h3 class="text-lg font-semibold">Resumen</h3>
            <p class="mt-2 text-gray-600">{{ $document->summary }}</p>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Entidades Extraídas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $entities = json_decode($document->extracted_entities, true) ?? [];
                    $colors = [
                        'person' => 'bg-blue-100 text-blue-800',
                        'organization' => 'bg-green-100 text-green-800',
                        'location' => 'bg-yellow-100 text-yellow-800',
                        'place' => 'bg-yellow-100 text-yellow-800', // Alias
                        'date' => 'bg-purple-100 text-purple-800',
                        'time' => 'bg-cyan-100 text-cyan-800',
                        'money' => 'bg-pink-100 text-pink-800',
                        'value' => 'bg-orange-100 text-orange-800',
                        'default' => 'bg-gray-100 text-gray-800',
                    ];
                    $translations = [
                        'person' => 'Persona',
                        'organization' => 'Organización',
                        'location' => 'Lugar',
                        'place' => 'Lugar', // Alias
                        'date' => 'Fecha',
                        'time' => 'Tiempo',
                        'money' => 'Valor Monetario',
                        'value' => 'Valor',
                    ];
                @endphp

                @forelse($entities as $entity)
                    @if(isset($entity['entity']) && isset($entity['type']))
                        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                            <h4 class="font-bold text-md text-gray-800 mb-2">{{ $entity['entity'] }}</h4>
                            @php
                                $type = strtolower($entity['type']);
                                $translatedType = $translations[$type] ?? ucfirst($type);
                                $colorClass = $colors[$type] ?? $colors['default'];
                            @endphp
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full {{ $colorClass }}">
                                {{ $translatedType }}
                            </span>
                        </div>
                    @endif
                @empty
                    <p class="text-gray-500 col-span-full">No se encontraron entidades en el documento.</p>
                @endforelse
            </div>
        </div>

        <!-- ❓ Pregunta personalizada -->
        <form method="POST" action="{{ route('documents.preguntar', $document->id) }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Selecciona el modelo de IA:</label>
                <div id="model-selector-pregunta" class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                     @foreach(config('openai.models') as $key => $value)
                        <div class="model-option-pregunta p-4 border rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50"
                             data-value="{{ $key }}">
                            <h4 class="font-semibold text-lg">{{ $value }}</h4>
                            <p class="text-sm text-gray-500">{{ $key === 'gpt-4o-mini' ? 'Más rápido y económico' : 'El más potente y preciso' }}</p>
                        </div>
                    @endforeach
                </div>
                <input type="hidden" name="model" id="model_pregunta">
            </div>
            <button type="submit"
                    class="bg-blue-900 text-white px-6 py-2 rounded transition duration-300">
                🧠 Preguntar a la IA
            </button>
        </form>
    @else
        <div id="analysis-pending-section">
            <p class="text-gray-500 text-sm mb-4">
                Este documento aún no ha sido analizado por IA.
            </p>
            <form id="analysis-form" method="POST" action="{{ route('documents.analizar', $document->id) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="question" value="Análisis General del Documento">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Selecciona el modelo de IA para el primer análisis:</label>
                    <div id="model-selector-analisis" class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach(config('openai.models') as $key => $value)
                            <div class="model-option p-4 border rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50"
                                 data-value="{{ $key }}">
                                <h4 class="font-semibold text-lg">{{ $value }}</h4>
                                <p class="text-sm text-gray-500">{{ $key === 'gpt-4o-mini' ? 'Más rápido y económico' : 'El más potente y preciso' }}</p>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="model" id="model_analisis">
                </div>
                <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded transition">
                    🧠 Analizar con IA
                </button>
            </form>
        </div>

        <!-- Barra de Progreso (inicialmente oculta) -->
        <div id="progress-section" class="hidden mt-6">
            <h4 class="text-lg font-semibold text-gray-700 mb-2">Analizando tu documento...</h4>
            <p class="text-sm text-gray-500 mb-4">Este proceso puede tardar unos minutos. Por favor, no cierres esta ventana.</p>
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div id="progress-bar" class="bg-blue-600 h-4 rounded-full transition-all duration-500" style="width: 0%"></div>
            </div>
            <p id="progress-text" class="text-center text-sm font-medium text-gray-600 mt-2">0%</p>
        </div>
    @endif
</div>

<div class="mt-8 border-t pt-4">
    <a href="{{ route('documents.index') }}" class="text-sm text-black-600 hover:underline">
        ← Volver a mis documentos
    </a>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Script for analysis model selector
        const modelSelectorAnalisis = document.getElementById('model-selector-analisis');
        if (modelSelectorAnalisis) {
            const modelInputAnalisis = document.getElementById('model_analisis');
            const modelOptionsAnalisis = modelSelectorAnalisis.querySelectorAll('.model-option');
            if (modelOptionsAnalisis.length > 0) {
                const defaultOption = modelOptionsAnalisis[0];
                modelInputAnalisis.value = defaultOption.dataset.value;
                defaultOption.classList.add('border-blue-500', 'bg-blue-50');
            }
            modelOptionsAnalisis.forEach(option => {
                option.addEventListener('click', () => {
                    modelOptionsAnalisis.forEach(opt => opt.classList.remove('border-blue-500', 'bg-blue-50'));
                    option.classList.add('border-blue-500', 'bg-blue-50');
                    modelInputAnalisis.value = option.dataset.value;
                });
            });
        }

        // Script for question model selector
        const modelSelectorPregunta = document.getElementById('model-selector-pregunta');
        if (modelSelectorPregunta) {
            const modelInputPregunta = document.getElementById('model_pregunta');
            const modelOptionsPregunta = modelSelectorPregunta.querySelectorAll('.model-option-pregunta');
            if (modelOptionsPregunta.length > 0) {
                const defaultOption = modelOptionsPregunta[0];
                modelInputPregunta.value = defaultOption.dataset.value;
                defaultOption.classList.add('border-blue-500', 'bg-blue-50');
            }
            modelOptionsPregunta.forEach(option => {
                option.addEventListener('click', () => {
                    modelOptionsPregunta.forEach(opt => opt.classList.remove('border-blue-500', 'bg-blue-50'));
                    option.classList.add('border-blue-500', 'bg-blue-50');
                    modelInputPregunta.value = option.dataset.value;
                });
            });
        }

        const analysisForm = document.getElementById('analysis-form');
        if (analysisForm) {
            analysisForm.addEventListener('submit', function (e) {
                e.preventDefault(); // Prevenir el envío normal del formulario

                // Ocultar el formulario y mostrar la barra de progreso
                document.getElementById('analysis-pending-section').classList.add('hidden');
                const progressSection = document.getElementById('progress-section');
                progressSection.classList.remove('hidden');

                const progressBar = document.getElementById('progress-bar');
                const progressText = document.getElementById('progress-text');
                let progress = 0;

                // Simular el progreso inicial para dar feedback inmediato
                const progressInterval = setInterval(() => {
                    if (progress < 90) {
                        progress += 5;
                        progressBar.style.width = progress + '%';
                        progressText.textContent = progress + '%';
                    } else {
                        clearInterval(progressInterval); // Detener en 90% hasta que el backend confirme
                    }
                }, 1000); // Aumenta 5% cada segundo

                // Enviar el formulario en segundo plano
                const formData = new FormData(analysisForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(analysisForm.action, {
                    method: 'POST',
                    credentials: 'same-origin', // ¡MUY IMPORTANTE! Envía las cookies de sesión.
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken, // Token leído desde la etiqueta meta.
                        'Accept': 'application/json' // Indicar que esperamos una respuesta JSON.
                    }
                }).then(response => {
                    if (response.ok) {
                        // Si el formulario se envió correctamente, empezar a verificar el estado
                        const checkStatusInterval = setInterval(() => {
                            fetch(`{{ route('documents.analysis_status', $document->id) }}`)
                                .then(res => res.json())
                                .then(data => {
                                    if (data.analysis_complete) {
                                        clearInterval(checkStatusInterval);
                                        clearInterval(progressInterval); // Asegurarse de detener el otro intervalo
                                        
                                        // Completar la barra y recargar
                                        progressBar.style.width = '100%';
                                        progressText.textContent = '100%';
                                        setTimeout(() => window.location.reload(), 500);
                                    }
                                });
                        }, 5000); // Verificar cada 5 segundos
                    } else {
                        // Si hay un error al enviar el formulario, mostrar un error
                        clearInterval(progressInterval);
                        progressSection.innerHTML = '<p class="text-red-500">Hubo un error al iniciar el análisis. Por favor, intenta recargar la página.</p>';
                    }
                }).catch(error => {
                    console.error('Error submitting form:', error);
                    clearInterval(progressInterval);
                    progressSection.innerHTML = '<p class="text-red-500">Hubo un error de red. Por favor, comprueba tu conexión e inténtalo de nuevo.</p>';
                });
            });
        }
    });
</script>

@if(session('status') && !$document->summary)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkStatus = setInterval(() => {
            fetch(`{{ route('documents.analysis_status', $document->id) }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.analysis_complete) {
                        clearInterval(checkStatus);
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error checking analysis status:', error);
                    clearInterval(checkStatus);
                });
        }, 5000); // Check every 5 seconds
    });
</script>
@endif
@endpush

@endsection
