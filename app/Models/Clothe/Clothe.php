<?php

namespace App\Models\Clothe;

use App\Models\Clothe\Traits\HasRelations;
use Database\Factories\ClotheFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[UseFactory(ClotheFactory::class)]
final class Clothe extends Model
{
    use HasFactory;
    use HasRelations;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'label',
        'category',
        'occasion',
        'season',
        'color_palette',
        'source_path',
        'image_path',
        'thumbnail_path',
        'ai_summary',
        'metadata',
        'is_favorite',
        'last_used_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'color_palette' => 'array',
        'ai_summary' => 'array',
        'metadata' => 'array',
        'is_favorite' => 'bool',
        'last_used_at' => 'datetime',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'image_url',
        'thumbnail_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $clothes): void {
            $clothes->uuid ??= (string) Str::ulid();
        });
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function markUsed(): void
    {
        $this->forceFill(['last_used_at' => now()])->saveQuietly();
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image_path ?? $this->source_path;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $path = $this->thumbnail_path ?? $this->image_path ?? $this->source_path;

        return $path ? Storage::disk('public')->url($path) : null;
    }
}
