@extends('layouts.base')

@section('title', 'Crear Cuenta')

@section('content')
<div class="text-center mb-6">
    <h2 class="text-3xl font-bold text-primary font-serif">Crea tu cuenta</h2>
    <p class="text-gray-600">Únete a la plataforma de gestión legal inteligente</p>
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

<form method="POST" action="{{ route('register') }}" class="space-y-5">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
        <input type="text" name="name" required
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
        <input type="email" name="email" required
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Contraseña</label>
        <input type="password" name="password" required
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Confirmar contraseña</label>
        <input type="password" name="password_confirmation" required
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
    </div>

    <div class="flex items-center">
        <input type="checkbox" name="terms" id="terms" required
            class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
        <label for="terms" class="ml-2 block text-sm text-gray-900">
            Acepto los
            <a href="{{ route('terms.conditions') }}" class="text-primary hover:underline">Términos y Condiciones</a> y el
            <a href="{{ route('privacy.policy') }}" class="text-primary hover:underline">Aviso de Privacidad</a>.
        </label>
    </div>

    <button type="submit"
        class="w-full bg-blue-900 text-white py-2 rounded-lg transition duration-300">
        Registrarse
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-600">
    ¿Ya tienes una cuenta?
    <a href="{{ route('login') }}" class="text-primary hover:underline">Inicia sesión</a>
</p>
@endsection
