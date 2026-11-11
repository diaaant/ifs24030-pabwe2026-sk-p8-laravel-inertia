<?php

namespace App\Features\Post\Models;

use App\Features\Post\Enums\PostCategory;
use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'category', 'excerpt', 'content',
        'cover_image_path', 'is_published', 'published_at',
    ];

    protected $appends = ['cover_url'];

    protected function casts(): array
    {
        return [
            'category' => PostCategory::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->cover_image_path
                ? Storage::disk('public')->url($this->cover_image_path)
                : null
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Pencarian portabel (PostgreSQL & SQLite) pada judul, ringkasan, dan isi. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = '%'.mb_strtolower($term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->whereRaw('LOWER(title) LIKE ?', [$like])
                ->orWhereRaw('LOWER(excerpt) LIKE ?', [$like])
                ->orWhereRaw('LOWER(content) LIKE ?', [$like]);
        });
    }
}
