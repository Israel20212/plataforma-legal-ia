@extends('layouts.base')

@section('title', 'Subir Documento')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-primary font-serif">📤 Subir nuevo documento</h2>
    <p class="text-gray-600">Llena los siguientes campos para cargar un archivo legal y analizarlo con IA.</p>
</div>

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
        <ul class="list-disc list-inside text-left text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700">Título del documento</label>
        <input type="text" name="titulo" required
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tipo de documento</label>
        <input type="text" name="tipo_documento" placeholder="Ej: Demanda, Sentencia, Contrato..."
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Descripción (opcional)</label>
        <textarea name="descripcion" rows="4"
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:outline-none"
            placeholder="Breve resumen o notas sobre el documento..."></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Archivo PDF</label>
        <input type="file" name="archivo" accept="application/pdf" required
            class="w-full mt-1 border border-gray-300 rounded-lg p-2 bg-white text-sm">
    </div>

    <div class="text-right">
        <button type="submit"
            class="bg-blue-900 text-white px-6 py-2 rounded transition duration-300">
            Subir Documento
        </button>
    </div>
</form>

<div class="mt-6">
    <a href="{{ route('documents.index') }}" class="text-primary hover:underline text-sm">
        ← Volver a mis documentos
    </a>
</div>
@endsection
