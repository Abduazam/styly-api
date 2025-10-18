<?php

namespace App\Models\Outfit;

use App\Models\Outfit\Traits\HasRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class Outfit extends Model
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
        'title',
        'description',
        'tag',
        'status',
        'image_path',
        'thumbnail_path',
        'prompt',
        'metadata',
        'is_favorite',
        'generated_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'prompt' => 'array',
        'metadata' => 'array',
        'is_favorite' => 'bool',
        'generated_at' => 'datetime',
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
        static::creating(function (self $outfit): void {
            $outfit->uuid ??= (string) Str::ulid();
        });
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function markFavorited(bool $value = true): void
    {
        $this->forceFill(['is_favorite' => $value])->saveQuietly();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $path = $this->thumbnail_path ?? $this->image_path;

        return $path ? Storage::disk('public')->url($path) : null;
    }
}
