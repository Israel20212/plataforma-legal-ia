@extends('layouts.base')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Panel de Administración</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Estadísticas</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Total Usuarios</p>
                    <p class="text-2xl font-bold">{{ $users->count() }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-600">Total Roles</p>
                    <p class="text-2xl font-bold">{{ $roles->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Acciones Rápidas</h2>
            <div class="space-y-4">
                <a href="{{ route('admin.users') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                    Gestionar Usuarios
                </a>
                <a href="{{ route('admin.roles') }}" class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                    Gestionar Roles
                </a>
            </div>
        </div>
    </div>
</div>
@endsection