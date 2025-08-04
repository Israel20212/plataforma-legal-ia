@extends('layouts.base')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="text-center mb-6">
    <h2 class="text-3xl font-bold text-primary font-serif">Bienvenido de nuevo</h2>
    <p class="text-gray-600">Accede a tu plataforma de gestión legal</p>
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

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
        <input type="email" name="email" required autofocus
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Contraseña</label>
        <input type="password" name="password" required
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
    </div>

    <button type="submit"
        class="w-full bg-blue-900 text-white py-2 rounded-lg transition duration-300">
        Iniciar sesión
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    ¿No tienes una cuenta?
    <a href="{{ route('register') }}" class="text-primary hover:underline">Regístrate</a>
</p>
@endsection
