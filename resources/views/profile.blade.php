@extends('layouts.base')

@section('title', 'Mi Perfil')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-primary font-serif">👤 Mi Perfil</h2>
    <p class="text-gray-600">Edita tu información personal y credenciales de acceso.</p>
</div>

@if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
        <ul class="list-disc list-inside text-left text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Nueva contraseña</label>
        <input type="password" name="password"
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Confirmar nueva contraseña</label>
        <input type="password" name="password_confirmation"
            class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm">
    </div>

    <div class="text-right">
        <button type="submit"
            class="bg-primary text-white px-6 py-2 rounded hover:bg-blue-900 transition duration-300">
            Guardar Cambios
        </button>
    </div>
</form>
@endsection
