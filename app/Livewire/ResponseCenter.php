<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\SavedResponse;
use Livewire\Component;

class ResponseCenter extends Component
{
    public string $search = '';
    public ?int $categoryId = null;
    public bool $favoritesOnly = false;

    public function selectCategory(?int $id): void
    {
        $this->categoryId = $id;
    }

    public function toggleFavorites(): void
    {
        $this->favoritesOnly = ! $this->favoritesOnly;
    }

    public function toggleFavorite(int $id): void
    {
        $response = SavedResponse::findOrFail($id);
        $response->update(['is_favorite' => ! $response->is_favorite]);
    }

    public function render()
    {
        $categories = Category::query()->withCount('responses')->orderBy('sort_order')->orderBy('name')->get();

        $responses = SavedResponse::query()
            ->with('category')
            ->search($this->search)
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->favoritesOnly, fn ($q) => $q->where('is_favorite', true))
            ->orderBy('title')
            ->get();

        return view('livewire.response-center', compact('categories', 'responses'))
            ->layout('components.layouts.app');
    }
}
