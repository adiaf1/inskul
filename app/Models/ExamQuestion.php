<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'exam_id',
        'question_text',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ExamOption::class)->orderBy('label');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }
}
