<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentAnalysis;
use App\Models\AnalysisLog;
use App\Models\Document;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        $documentsByYear = $query->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function($date) {
                return $date->created_at->year;
            });

        return view('documents.index', compact('documentsByYear'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_documento' => 'nullable|string|max:100',
            'archivo' => 'required|file|mimes:pdf|max:20480', // 20 MB
        ]);

        $archivoPath = $request->file('archivo')->store('documentos', 'public');

        $document = Document::create([
            'user_id' => Auth::id(),
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo_documento' => $request->tipo_documento,
            'archivo' => $archivoPath,
        ]);

        // Process document with AI after creation
        $this->processDocumentAI($document);

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

        // Re-process document with AI after update if content might have changed (e.g., if file was re-uploaded, though not implemented here)
        // For now, we'll just re-process to ensure summary/entities are up-to-date if the document itself was modified.
        $this->processDocumentAI($document);

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

    private function processDocumentAI(Document $document)
    {
        try {
            $pdfPath = storage_path("app/public/" . $document->archivo);
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();

            // 1. Resumen (chunkable y con tokens optimizados)
            $summaryPrompt = "Summarize the key points of the following text in under 300 words:

---
" . $text;
            $summaryResponse = $this->openAIService->callOpenAIAPI($summaryPrompt, null, 450, 0.5, true);
            $document->summary = $summaryResponse['choices'][0]['message']['content'] ?? 'Could not generate summary.';

            // 2. Extracción de Entidades (chunkable y con tokens optimizados)
            $entitiesPrompt = "Extract named entities (people, organizations, locations, dates, monetary values) from this text. Return as a JSON array of objects with 'entity' and 'type' keys. Example: [{'entity': 'John Doe', 'type': 'Person'}]:

---
" . $text;
            $entitiesResponse = $this->openAIService->callOpenAIAPI($entitiesPrompt, null, 500, 0.5, true);
            $entitiesContent = $entitiesResponse['choices'][0]['message']['content'] ?? '[]';
            $document->extracted_entities = json_decode($entitiesContent, true);

            $document->save();

        } catch (\Exception $e) {
            Log::error('Error processing document with OpenAI: ' . $e->getMessage());
        }
    }

    public function analizar(Request $request, Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'model' => 'required|string|in:' . implode(',', array_keys(config('openai.models'))),
            'question' => 'nullable|string|max:1000', // Allow specific questions
        ]);

        $model = $request->input('model');
        // Use the provided question or default to a general analysis
        $question = $request->input('question', 'Análisis General del Documento');

        ProcessDocumentAnalysis::dispatch($document, $model, $question);

        return back()->with('status', 'El análisis ha comenzado. Los resultados aparecerán en breve.');
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
