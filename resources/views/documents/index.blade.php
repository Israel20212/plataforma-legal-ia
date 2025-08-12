@extends('layouts.base')

@section('title', 'Mis Documentos')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-primary font-serif">📁 Mis Documentos</h2>
    <p class="text-gray-600">Aquí puedes consultar, analizar y gestionar tus archivos legales.</p>
</div>

<div class="mb-4 text-right">
    <a href="{{ route('documents.create') }}"
       class="inline-block bg-blue-900 text-white px-4 py-2 rounded transition">
        + Nuevo Documento
    </a>
</div>

<div class="mb-6">
    <form action="{{ route('documents.index') }}" method="GET" class="flex items-center space-x-2">
        <input type="text" name="search" placeholder="Buscar documentos..."
               class="flex-grow px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
               value="{{ request('search') }}">
        <button type="submit" class="bg-blue-900 text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition">
            Buscar
        </button>
        @if(request('search'))
            <a href="{{ route('documents.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition">
                Limpiar
            </a>
        @endif
    </form>
</div>

@if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if($documentsByDate->count())
    @foreach($documentsByDate as $date => $documents)
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4">{{ $date }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($documents as $doc)
                <div class="bg-white border border-gray-200 rounded-lg shadow p-5 hover:shadow-md transition">
                    <h3 class="text-lg font-semibold text-secondary mb-1">{{ $doc->titulo }}</h3>
                    <p class="text-sm text-gray-500 mb-2"><strong>Tipo:</strong> {{ $doc->tipo_documento ?? 'No especificado' }}</p>
                    <p class="text-gray-600 text-sm line-clamp-2">{{ $doc->descripcion }}</p>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('documents.show', $doc) }}"
                           class="bg-blue-900 text-white text-sm px-3 py-1 rounded transition">
                            Ver Detalles
                        </a>
                        <a href="{{ route('documents.edit', $doc) }}"
                           class="bg-yellow-600 text-white text-sm px-3 py-1 rounded hover:bg-yellow-700 transition">
                            Editar
                        </a>
                        <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-600 text-white text-sm px-3 py-1 rounded hover:bg-red-700 transition"
                                    onclick="return confirm('¿Estás seguro de eliminar este documento?')">
                                Eliminar
                            </button>
                        </form>
                        <a href="{{ route('documents.stream', $doc->id) }}" target="_blank"
                           class="bg-gray-300 text-gray-800 text-sm px-3 py-1 rounded hover:bg-gray-400 transition">
                            Ver PDF
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@else
    <p class="text-gray-600\">Aún no has subido ningún documento.</p>
@endif
@endsection
