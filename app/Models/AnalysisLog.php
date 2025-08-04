<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalysisLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'question',
        'answer',
        'resultado',
        'modelo',
        'tokens',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
