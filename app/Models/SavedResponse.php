<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedResponse extends Model
{
    protected $fillable = ['category_id', 'title', 'content', 'keywords', 'is_favorite', 'source'];

    protected function casts(): array
    {
        return ['keywords' => 'array', 'is_favorite' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $query;

        return $query->where(function (Builder $q) use ($term) {
            $like = '%'.$term.'%';
            $q->where('title', 'like', $like)
              ->orWhere('content', 'like', $like)
              ->orWhere('keywords', 'like', $like);
        });
    }
}
