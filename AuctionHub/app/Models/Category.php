<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'parent_id'];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    // Scope to get all descendants recursively
    public function scopeDescendants($query, $categoryId)
    {
        return $query->where('parent_id', $categoryId)
                     ->orWhereIn('parent_id', function ($subQuery) use ($categoryId) {
                         $subQuery->select('id')
                                  ->from('categories')
                                  ->where('parent_id', $categoryId);
                     });
    }

    // Helper method to get all descendants IDs
    public function getDescendantIds(): array
    {
        $ids = [$this->id];
        $children = $this->children;

        foreach ($children as $child) {
            $ids = array_merge($ids, $child->getDescendantIds());
        }

        return $ids;
    }
}
