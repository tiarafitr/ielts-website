<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    /** Eloquent would pluralise "Feedback" to the uncountable "feedback". */
    protected $table = 'feedbacks';

    protected $fillable = [
        'attempt_id',
        'band_score',
        'criteria',
        'strengths',
        'areas_to_improve',
        'raw_response',
    ];

    protected $casts = [
        'band_score' => 'float',
        'criteria' => 'array',
        'strengths' => 'array',
        'areas_to_improve' => 'array',
        'raw_response' => 'array',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }
}
