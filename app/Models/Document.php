<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titulo',
        'descripcion',
        'archivo',
        'tipo_documento',
        'created_at',
        'summary', // Nuevo campo para el resumen
        'extracted_entities', // Nuevo campo para las entidades extraídas
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function analysisLogs()
    {
        return $this->hasMany(AnalysisLog::class);
    }
}
