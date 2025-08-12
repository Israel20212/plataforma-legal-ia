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

        $archivoPath = Storage::disk('documents')->putFile('', $request->file('archivo'));

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

        if (!Storage::disk('documents')->exists($document->archivo)) {
            abort(404, 'Archivo no encontrado.');
        }

        $path = Storage::disk('documents')->path($document->archivo);

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

        Storage::disk('documents')->delete($document->archivo);
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Documento eliminado correctamente.');
    }

    public function analizar(Request $request, Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        // 1. Marcar que el análisis está en proceso
        $document->update([
            'summary' => null,
            'extracted_entities' => null,
            'analysis_complete' => false,
        ]);

        // 2. Despachar el job solo con el ID del documento y los parámetros necesarios
        ProcessDocumentAnalysis::dispatch(
            $document->id,
            $request->input('model'),
            $request->input('question', 'Análisis General del Documento')
        );

        // 3. Devolver una respuesta JSON que el frontend espera
        return response()->json(['status' => 'analysis_queued'], 202);
    }

    public function checkAnalysisStatus(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Refrescar el modelo para obtener el estado más reciente de la base de datos
        $document->refresh();

        return response()->json(['analysis_complete' => (bool)$document->analysis_complete]);
    }
}
