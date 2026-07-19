<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentoOperativo extends Model
{
    protected $table = 'documentos_operativos';

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'user_id',
        'titulo',
        'descripcion',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sizeLabel(): string
    {
        $size = max(0, (int) $this->size);

        if ($size >= 1048576) {
            return number_format($size / 1048576, 1, ',', '.') . ' MB';
        }

        if ($size >= 1024) {
            return number_format($size / 1024, 1, ',', '.') . ' KB';
        }

        return $size . ' B';
    }
}
