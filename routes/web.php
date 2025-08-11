<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;

// Redirigir raíz al login
Route::get('/', function () {
    return redirect()->route('login');
});

// 🔐 Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Página de Aviso de Privacidad
Route::get('/aviso-de-privacidad', function () {
    return view('privacy-policy');
})->name('privacy.policy');

// Página de Términos y Condiciones
Route::get('/terminos-y-condiciones', function () {
    return view('terms-and-conditions');
})->name('terms.conditions');

// 🧭 Dashboard protegido
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// 🔒 Grupo de rutas protegidas por login
Route::middleware('auth')->group(function () {
    // 📄 Documentos
    Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documentos/crear', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documentos', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documentos/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documentos/{document}/stream', [DocumentController::class, 'streamFile'])->name('documents.stream');
    Route::get('/documentos/{document}/editar', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('/documentos/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documentos/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::post('/documentos/{document}/analizar', [DocumentController::class, 'analizar'])->name('documents.analizar');
    Route::get('/documentos/{document}/status', [DocumentController::class, 'checkAnalysisStatus'])->name('documents.analysis_status');
    Route::post('/documentos/{document}/preguntar', [DocumentController::class, 'preguntarDocumento'])->name('documents.preguntar');

    // 👤 Perfil de usuario
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/perfil', [ProfileController::class, 'update'])->name('profile.update');
});
