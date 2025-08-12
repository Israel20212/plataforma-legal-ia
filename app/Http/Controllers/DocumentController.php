<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessDocumentAnalysis;
use App\Models\AnalysisLog;
use App\Services\OpenAIService;
use Smalot\PdfParser\Parser;

class DocumentController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function index(Request $request)
    {
        $query = Document::where('user_id', Auth::id());

        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('titulo', 'like', $searchTerm)
                  ->orWhere('descripcion', 'like', $searchTerm)
                  ->orWhere('tipo_documento', 'like', $searchTerm);
            });
        }

        $documentsByDate = $query->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($date) {
                return $date->created_at->format('d-m-Y');
            });

        return view('documents.index', ['documentsByDate' => $documentsByDate]);
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        // Aumentar el tiempo de ejecución a 5 minutos (300 segundos) para esta operación
        set_time_limit(300);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_documento' => 'nullable|string|max:100',
            'archivo' => 'required|file|mimes:pdf|max:20480', // 20 MB
        ]);

        $archivoPath = $request->file('archivo')->store('documentos', 'public');

        Document::create([
            'user_id' => Auth::id(),
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo_documento' => $request->tipo_documento,
            'archivo' => $archivoPath,
        ]);

        return redirect()->route('documents.index')->with('success', 'Documento subido correctamente.');
    }

    public function show(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        return view('documents.show', compact('document'));
    }

    public function streamFile(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403, 'No autorizado para ver este archivo.');
        }

        $path = storage_path('app/public/' . $document->archivo);

        if (!Storage::disk('public')->exists($document->archivo)) {
            abort(404, 'Archivo no encontrado.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }

    public function edit(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_documento' => 'nullable|string|max:100',
            'fecha_creacion' => 'nullable|date',
        ]);

        $updateData = [
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo_documento' => $request->tipo_documento,
        ];

        if ($request->filled('fecha_creacion')) {
            $updateData['created_at'] = $request->fecha_creacion;
        }

        $document->update($updateData);

        return redirect()->route('documents.index')->with('success', 'Documento actualizado correctamente.');
    }

    public function destroy(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::disk('public')->delete($document->archivo);
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Documento eliminado correctamente.');
    }

    public function analizar(Request $request, Document $document)
    {
        // Aumentar el tiempo de ejecución a 5 minutos (300 segundos) para esta operación
        set_time_limit(300);

        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'model' => 'required|string|in:' . implode(',', array_keys(config('openai.models'))),
            'question' => 'nullable|string|max:1000', // Allow specific questions
        ]);

        try {
            $model = $request->input('model');
            $question = $request->input('question', 'Análisis General del Documento');

            ProcessDocumentAnalysis::dispatch($document, $model, $question);

            return back()->with('status', 'El análisis ha comenzado. Los resultados aparecerán en breve.');

        } catch (\Exception $e) {
            Log::error('Error dispatching analysis job: ' . $e->getMessage());
            return back()->with('error', 'No se pudo iniciar el análisis. Por favor, inténtelo de nuevo más tarde.');
        }
    }

    public function checkAnalysisStatus(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $isComplete = $document->summary && $document->extracted_entities;

        return response()->json(['analysis_complete' => $isComplete]);
    }
}
