<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Plataforma Legal')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 font-sans">

    <!-- 🔵 NAVBAR -->
    <nav class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-4">
                    <span class="text-xl font-bold text-primary font-serif">⚖️ LexIA</span>

                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-primary text-sm">Dashboard</a>
                        <a href="{{ route('documents.index') }}" class="text-gray-700 hover:text-primary text-sm">Documentos</a>
                        <a href="{{ route('profile.edit') }}" class="text-gray-700 hover:text-primary text-sm">Perfil</a>
                        @role('admin')
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:bg-blue-100 text-sm font-semibold bg-blue-50 px-3 py-1 rounded-md">Panel Admin</a>
                        @endrole
                    @endauth
                </div>

                @auth
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">👤 {{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="text-sm bg-red-600 text-white px-3 py-1 rounded hover:bg-red-800">Salir</button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- 🔽 CONTENIDO -->
    <main class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
