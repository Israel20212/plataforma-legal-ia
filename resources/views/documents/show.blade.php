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

        <div class="mt-4">
            <h3 class="text-lg font-semibold">Entidades Extraídas</h3>
            <ul class="mt-2 text-gray-600 list-disc list-inside">
                @foreach(json_decode($document->extracted_entities, true) as $entityType => $entities)
                    <li><strong>{{ ucfirst($entityType) }}:</strong>
                        <ul class="list-disc list-inside ml-4">
                            @foreach($entities as $entity)
                                <li>{{ $entity }}</li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- ❓ Pregunta personalizada -->
        <form method="POST" action="{{ route('documents.preguntar', $document->id) }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="pregunta" class="block text-sm font-medium text-gray-700">Haz una nueva pregunta al documento:</label>
                <input type="text" id="pregunta" name="pregunta" required
                       placeholder="Ej. ¿Qué sanción se menciona en el documento?"
                       class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:outline-none">
            </div>
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
        <p class="text-gray-500 text-sm mb-4">
            Este documento aún no ha sido analizado por IA.
        </p>
        <form method="POST" action="{{ route('documents.analizar', $document->id) }}" class="space-y-4">
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
