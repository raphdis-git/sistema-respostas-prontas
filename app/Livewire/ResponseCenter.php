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

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $title = '';
    public ?int $formCategoryId = null;
    public string $keywords = '';
    public string $content = '';
    public bool $isFavorite = false;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'formCategoryId' => ['nullable', 'exists:categories,id'],
            'keywords' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'isFavorite' => ['boolean'],
        ];
    }

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

    public function createResponse(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editResponse(int $id): void
    {
        $response = SavedResponse::findOrFail($id);

        $this->editingId = $response->id;
        $this->title = $response->title;
        $this->formCategoryId = $response->category_id;
        $this->keywords = $response->keywords ?? '';
        $this->content = $response->content;
        $this->isFavorite = (bool) $response->is_favorite;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function saveResponse(): void
    {
        $this->validate();

        $data = [
            'title' => trim($this->title),
            'category_id' => $this->formCategoryId,
            'keywords' => trim($this->keywords) ?: null,
            'content' => $this->content,
            'is_favorite' => $this->isFavorite,
        ];

        if ($this->editingId) {
            SavedResponse::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Resposta atualizada com sucesso.');
        } else {
            SavedResponse::create($data);
            session()->flash('message', 'Resposta cadastrada com sucesso.');
        }

        $this->closeForm();
    }

    public function deleteResponse(int $id): void
    {
        SavedResponse::findOrFail($id)->delete();
        session()->flash('message', 'Resposta excluída com sucesso.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->formCategoryId = null;
        $this->keywords = '';
        $this->content = '';
        $this->isFavorite = false;
        $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('responses')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

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
