<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\SavedResponse;
use App\Services\WordResponseImporter;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class ResponseCenter extends Component
{
    use WithFileUploads;

    public string $search=''; public ?int $categoryId=null; public bool $favoritesOnly=false;
    public bool $showForm=false; public ?int $editingId=null; public string $title=''; public ?int $formCategoryId=null; public string $keywords=''; public string $content=''; public bool $isFavorite=false;
    public bool $showCategoryManager=false; public ?int $editingCategoryId=null; public string $categoryName=''; public ?int $parentCategoryId=null;
    public bool $showImporter=false; public $wordFile; public array $importPreview=[]; public ?int $importCategoryId=null;

    protected function rules(): array { return ['title'=>['required','string','max:255'],'formCategoryId'=>['nullable','exists:categories,id'],'keywords'=>['nullable','string','max:1000'],'content'=>['required','string'],'isFavorite'=>['boolean']]; }
    public function selectCategory(?int $id):void{$this->categoryId=$id;} public function toggleFavorites():void{$this->favoritesOnly=!$this->favoritesOnly;}
    public function toggleFavorite(int $id):void{$r=SavedResponse::findOrFail($id);$r->update(['is_favorite'=>!$r->is_favorite]);}
    public function createResponse():void{$this->resetForm();$this->showForm=true;}
    public function editResponse(int $id):void{$r=SavedResponse::findOrFail($id);$this->editingId=$r->id;$this->title=$r->title;$this->formCategoryId=$r->category_id;$this->keywords=is_array($r->keywords)?implode(', ',$r->keywords):($r->keywords??'');$this->content=$r->content;$this->isFavorite=(bool)$r->is_favorite;$this->showForm=true;$this->resetValidation();}
    public function saveResponse():void{$this->validate();$d=['title'=>trim($this->title),'category_id'=>$this->formCategoryId,'keywords'=>trim($this->keywords)?:null,'content'=>$this->content,'is_favorite'=>$this->isFavorite];if($this->editingId){SavedResponse::findOrFail($this->editingId)->update($d);session()->flash('message','Resposta atualizada com sucesso.');}else{SavedResponse::create($d);session()->flash('message','Resposta cadastrada com sucesso.');}$this->closeForm();}
    public function deleteResponse(int $id):void{SavedResponse::findOrFail($id)->delete();session()->flash('message','Resposta excluída com sucesso.');}
    public function closeForm():void{$this->showForm=false;$this->resetForm();} private function resetForm():void{$this->editingId=null;$this->title='';$this->formCategoryId=null;$this->keywords='';$this->content='';$this->isFavorite=false;$this->resetValidation();}

    public function openCategoryManager():void{$this->showCategoryManager=true;$this->resetCategoryForm();} public function closeCategoryManager():void{$this->showCategoryManager=false;$this->resetCategoryForm();}
    public function editCategory(int $id):void{$c=Category::findOrFail($id);$this->editingCategoryId=$c->id;$this->categoryName=$c->name;$this->parentCategoryId=$c->parent_id;$this->showCategoryManager=true;$this->resetValidation();}
    public function saveCategory():void{$this->validate(['categoryName'=>['required','string','max:255',Rule::unique('categories','name')->ignore($this->editingCategoryId)],'parentCategoryId'=>['nullable','exists:categories,id',Rule::notIn(array_filter([$this->editingCategoryId]))]]);$d=['name'=>trim($this->categoryName),'parent_id'=>$this->parentCategoryId];if($this->editingCategoryId){Category::findOrFail($this->editingCategoryId)->update($d);session()->flash('message','Categoria atualizada com sucesso.');}else{Category::create($d);session()->flash('message','Categoria cadastrada com sucesso.');}$this->resetCategoryForm();}
    public function deleteCategory(int $id):void{$c=Category::withCount(['responses','children'])->findOrFail($id);if($c->responses_count>0||$c->children_count>0){session()->flash('error','Esta categoria possui respostas ou subcategorias. Mova ou exclua esses itens antes de apagar a categoria.');return;}$c->delete();if($this->categoryId===$id)$this->categoryId=null;$this->resetCategoryForm();session()->flash('message','Categoria excluída com sucesso.');}
    private function resetCategoryForm():void{$this->editingCategoryId=null;$this->categoryName='';$this->parentCategoryId=null;$this->resetValidation();}

    public function openImporter():void{$this->showImporter=true;$this->importPreview=[];$this->wordFile=null;$this->importCategoryId=null;$this->resetValidation();}
    public function closeImporter():void{$this->showImporter=false;$this->importPreview=[];$this->wordFile=null;$this->importCategoryId=null;$this->resetValidation();}
    public function previewImport(WordResponseImporter $importer):void
    {
        $this->validate(['wordFile'=>['required','file','mimes:docx','max:10240'],'importCategoryId'=>['nullable','exists:categories,id']]);
        try{$this->importPreview=$importer->parse($this->wordFile->getRealPath());if(!$this->importPreview)session()->flash('error','Nenhum título em negrito + sublinhado foi encontrado no arquivo.');}
        catch(\Throwable $e){$this->importPreview=[];session()->flash('error','Não foi possível ler o arquivo Word. Verifique se ele é um .docx válido.');}
    }
    public function confirmImport():void
    {
        if(!$this->importPreview){session()->flash('error','Gere a prévia antes de importar.');return;}
        $created=0;$skipped=0;
        foreach($this->importPreview as $item){if(SavedResponse::where('title',$item['title'])->exists()){$skipped++;continue;}SavedResponse::create(['title'=>$item['title'],'content'=>$item['content'],'category_id'=>$this->importCategoryId,'keywords'=>null,'is_favorite'=>false]);$created++;}
        $this->closeImporter();session()->flash('message',"Importação concluída: {$created} respostas criadas".($skipped?" e {$skipped} títulos já existentes ignorados.":'.'));
    }

    public function render()
    {
        $categories=Category::query()->with('parent')->withCount('responses')->orderBy('sort_order')->orderBy('name')->get();
        $responses=SavedResponse::query()->with('category')->search($this->search)->when($this->categoryId,function($q){$ids=Category::where('id',$this->categoryId)->orWhere('parent_id',$this->categoryId)->pluck('id');$q->whereIn('category_id',$ids);})->when($this->favoritesOnly,fn($q)=>$q->where('is_favorite',true))->orderBy('title')->get();
        return view('livewire.response-center',compact('categories','responses'))->layout('components.layouts.app');
    }
}
