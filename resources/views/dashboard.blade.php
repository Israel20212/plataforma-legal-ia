@extends('layouts.base')

@section('title', 'Panel Principal')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-primary font-serif">Bienvenido, {{ Auth::user()->name }}</h2>
    <p class="text-gray-600">Accede a tus documentos, casos y herramientas legales inteligentes.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Acceso a documentos -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow hover:shadow-md transition">
        <h3 class="text-lg font-semibold text-secondary mb-2">📄 Mis Documentos</h3>
        <p class="text-gray-500 mb-4">Consulta, gestiona y analiza tus archivos legales con IA.</p>
        <a href="{{ route('documents.index') }}" class="inline-block bg-blue-900 text-white px-4 py-2 rounded">
            Ver Documentos
        </a>
    </div>

    <!-- Cerrar sesión -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow hover:shadow-md transition">
        <h3 class="text-lg font-semibold text-secondary mb-2">🔒 Cerrar sesión</h3>
        <p class="text-gray-500 mb-4">Salir de tu cuenta de manera segura.</p>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-800">
                Salir
            </button>
        </form>
    </div>
</div>
@endsection
