<?php

namespace App\Models;

use App\AnalysisSection;
use Database\Factories\FaceAnalysisSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một phần của bản luận giải. `content` là JSON có cấu trúc do Gemini trả về,
 * null khi phần này chưa được sinh (phần trả phí chỉ sinh sau khi thanh toán).
 */
#[Fillable(['type', 'content', 'generated_at', 'unlocked_at'])]
class FaceAnalysisSection extends Model
{
    /** @use HasFactory<FaceAnalysisSectionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AnalysisSection::class,
            'content' => 'array',
            'generated_at' => 'datetime',
            'unlocked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<FaceAnalysis, $this>
     */
    public function faceAnalysis(): BelongsTo
    {
        return $this->belongsTo(FaceAnalysis::class);
    }

    public function isUnlocked(): bool
    {
        return $this->type->isFree() || $this->unlocked_at !== null;
    }

    public function isGenerated(): bool
    {
        return $this->generated_at !== null;
    }
}
