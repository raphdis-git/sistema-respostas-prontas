<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\SavedResponse;
use Illuminate\Validation\Rule;
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

    public bool $showCategoryManager = false;
    public ?int $editingCategoryId = null;
    public string $categoryName = '';
    public ?int $parentCategoryId = null;

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

    public function selectCategory(?int $id): void { $this->categoryId = $id; }
    public function toggleFavorites(): void { $this->favoritesOnly = ! $this->favoritesOnly; }

    public function toggleFavorite(int $id): void
    {
        $response = SavedResponse::findOrFail($id);
        $response->update(['is_favorite' => ! $response->is_favorite]);
    }

    public function createResponse(): void { $this->resetForm(); $this->showForm = true; }

    public function editResponse(int $id): void
    {
        $response = SavedResponse::findOrFail($id);
        $this->editingId = $response->id;
        $this->title = $response->title;
        $this->formCategoryId = $response->category_id;
        $this->keywords = is_array($response->keywords) ? implode(', ', $response->keywords) : ($response->keywords ?? '');
        $this->content = $response->content;
        $this->isFavorite = (bool) $response->is_favorite;
        $this->showForm = true;
        $this->resetValidation();
    }

    public function saveResponse(): void
    {
        $this->validate();
        $data = ['title'=>trim($this->title),'category_id'=>$this->formCategoryId,'keywords'=>trim($this->keywords) ?: null,'content'=>$this->content,'is_favorite'=>$this->isFavorite];
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

    public function closeForm(): void { $this->showForm = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->editingId = null; $this->title = ''; $this->formCategoryId = null; $this->keywords = ''; $this->content = ''; $this->isFavorite = false; $this->resetValidation();
    }

    public function openCategoryManager(): void
    {
        $this->showCategoryManager = true;
        $this->resetCategoryForm();
    }

    public function closeCategoryManager(): void
    {
        $this->showCategoryManager = false;
        $this->resetCategoryForm();
    }

    public function editCategory(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name;
        $this->parentCategoryId = $category->parent_id;
        $this->showCategoryManager = true;
        $this->resetValidation();
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryName' => ['required','string','max:255', Rule::unique('categories','name')->ignore($this->editingCategoryId)],
            'parentCategoryId' => ['nullable','exists:categories,id', Rule::notIn(array_filter([$this->editingCategoryId]))],
        ]);

        $data = ['name' => trim($this->categoryName), 'parent_id' => $this->parentCategoryId];
        if ($this->editingCategoryId) {
            Category::findOrFail($this->editingCategoryId)->update($data);
            session()->flash('message', 'Categoria atualizada com sucesso.');
        } else {
            Category::create($data);
            session()->flash('message', 'Categoria cadastrada com sucesso.');
        }
        $this->resetCategoryForm();
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::withCount(['responses','children'])->findOrFail($id);
        if ($category->responses_count > 0 || $category->children_count > 0) {
            session()->flash('error', 'Esta categoria possui respostas ou subcategorias. Mova ou exclua esses itens antes de apagar a categoria.');
            return;
        }
        $category->delete();
        if ($this->categoryId === $id) $this->categoryId = null;
        $this->resetCategoryForm();
        session()->flash('message', 'Categoria excluída com sucesso.');
    }

    private function resetCategoryForm(): void
    {
        $this->editingCategoryId = null; $this->categoryName = ''; $this->parentCategoryId = null; $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::query()->with('parent')->withCount('responses')->orderBy('sort_order')->orderBy('name')->get();
        $responses = SavedResponse::query()->with('category')->search($this->search)
            ->when($this->categoryId, function ($q) {
                $ids = Category::where('id', $this->categoryId)->orWhere('parent_id', $this->categoryId)->pluck('id');
                $q->whereIn('category_id', $ids);
            })
            ->when($this->favoritesOnly, fn ($q) => $q->where('is_favorite', true))->orderBy('title')->get();

        return view('livewire.response-center', compact('categories', 'responses'))->layout('components.layouts.app');
    }
}
