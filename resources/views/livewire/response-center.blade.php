<div>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><h1 class="text-2xl font-bold tracking-tight">Respostas prontas</h1><p class="mt-1 text-sm text-slate-500">Selecione uma categoria ou pesquise diretamente.</p></div>
        <div class="flex flex-wrap gap-2"><button wire:click="openCategoryManager" type="button" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold hover:bg-slate-50">Gerenciar categorias</button><button wire:click="createResponse" type="button" class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">+ Nova resposta</button></div>
    </header>

    @if(session()->has('message'))<div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('message') }}</div>@endif
    @if(session()->has('error'))<div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>@endif

    @if($showCategoryManager)
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-semibold">Categorias e subcategorias</h2><p class="mt-1 text-sm text-slate-500">Organize as respostas em áreas. Uma categoria pode ficar dentro de outra.</p></div><button wire:click="closeCategoryManager" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Fechar</button></div>
            <form wire:submit="saveCategory" class="mt-5 grid gap-3 md:grid-cols-[1fr_1fr_auto] md:items-start">
                <div><label class="text-sm font-medium">Nome</label><input wire:model="categoryName" type="text" placeholder="Ex.: PCLAB Online" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3">@error('categoryName')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                <div><label class="text-sm font-medium">Categoria principal</label><select wire:model="parentCategoryId" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"><option value="">Nenhuma (categoria principal)</option>@foreach($categories->whereNull('parent_id') as $category)@if($category->id !== $editingCategoryId)<option value="{{ $category->id }}">{{ $category->name }}</option>@endif @endforeach</select></div>
                <button type="submit" class="mt-6 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white">{{ $editingCategoryId ? 'Salvar' : '+ Adicionar' }}</button>
            </form>
            @if($editingCategoryId)<button wire:click="openCategoryManager" type="button" class="mt-2 text-sm text-slate-500 underline">Cancelar edição</button>@endif
            <div class="mt-6 divide-y divide-slate-100 rounded-xl border border-slate-200">
                @forelse($categories as $category)
                    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0"><div class="font-medium">@if($category->parent)<span class="text-slate-400">{{ $category->parent->name }} › </span>@endif{{ $category->name }}</div><div class="mt-1 text-xs text-slate-500">{{ $category->responses_count }} {{ $category->responses_count === 1 ? 'resposta' : 'respostas' }}</div></div>
                        <div class="flex gap-2"><button wire:click="editCategory({{ $category->id }})" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Editar</button><button wire:click="deleteCategory({{ $category->id }})" wire:confirm="Tem certeza que deseja excluir esta categoria?" class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-600">Excluir</button></div>
                    </div>
                @empty<div class="p-5 text-center text-sm text-slate-500">Nenhuma categoria cadastrada.</div>@endforelse
            </div>
        </section>
    @endif

    @if($showForm)
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4"><div><h2 class="text-lg font-semibold">{{ $editingId ? 'Editar resposta' : 'Nova resposta' }}</h2><p class="mt-1 text-sm text-slate-500">Preencha os dados abaixo. As palavras-chave ajudam na pesquisa.</p></div><button wire:click="closeForm" type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">Fechar</button></div>
            <form wire:submit="saveResponse" class="mt-5 grid gap-4">
                <div><label class="text-sm font-medium">Título</label><input wire:model="title" type="text" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3">@error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                <div class="grid gap-4 md:grid-cols-2"><div><label class="text-sm font-medium">Categoria</label><select wire:model="formCategoryId" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"><option value="">Sem categoria</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->parent ? $category->parent->name.' › ' : '' }}{{ $category->name }}</option>@endforeach</select></div><div><label class="text-sm font-medium">Palavras-chave</label><input wire:model="keywords" type="text" placeholder="Ex.: nfe, sefaz, certificado" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3"><p class="mt-1 text-xs text-slate-400">Separe as palavras por vírgula.</p></div></div>
                <div><label class="text-sm font-medium">Resposta</label><textarea wire:model="content" rows="8" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3"></textarea>@error('content')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                <label class="flex items-center gap-2 text-sm"><input wire:model="isFavorite" type="checkbox" class="rounded border-slate-300">Marcar como favorita</label>
                <div class="flex justify-end gap-2"><button wire:click="closeForm" type="button" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium">Cancelar</button><button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white">{{ $editingId ? 'Salvar alterações' : 'Cadastrar resposta' }}</button></div>
            </form>
        </section>
    @endif

    <div class="mt-6"><input wire:model.live.debounce.250ms="search" type="search" placeholder="Buscar por título, conteúdo ou palavra-chave..." class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"></div>

    <section class="mt-7">
        <div class="mb-3 flex items-center justify-between"><h2 class="font-semibold">Categorias</h2><button wire:click="selectCategory(null)" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">Todas</button></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($categories->whereNull('parent_id') as $category)
                <button wire:click="selectCategory({{ $category->id }})" class="min-h-20 rounded-xl border p-4 text-left transition {{ $categoryId === $category->id ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'border-slate-200 bg-white hover:border-slate-300' }}"><span class="block font-semibold leading-5">{{ $category->name }}</span><span class="mt-1.5 block text-xs text-slate-500">{{ $category->responses_count }} respostas diretas@if($categories->where('parent_id',$category->id)->count()) · {{ $categories->where('parent_id',$category->id)->count() }} subcategorias@endif</span></button>
            @endforeach
        </div>
    </section>

    <section class="mt-8">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3"><div><h2 class="font-semibold">{{ $categoryId ? optional($categories->firstWhere('id',$categoryId))->name : ($favoritesOnly ? 'Favoritos' : 'Todas as respostas') }}</h2><p class="text-sm text-slate-500">{{ $responses->count() }} {{ $responses->count() === 1 ? 'resposta encontrada' : 'respostas encontradas' }}</p></div><button wire:click="toggleFavorites" class="rounded-lg border px-3 py-2 text-sm {{ $favoritesOnly ? 'border-amber-300 bg-amber-50 text-amber-800' : 'border-slate-300 bg-white' }}">★ Favoritos</button></div>
        <div class="space-y-3">
            @forelse($responses as $response)
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-start justify-between gap-4"><div class="min-w-0 flex-1">@if($response->category)<div class="text-xs font-medium text-slate-500">{{ $response->category->parent ? $response->category->parent->name.' › ' : '' }}{{ $response->category->name }}</div>@endif<h3 class="mt-1 text-lg font-semibold">{{ $response->title }}</h3><div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $response->content }}</div></div><button wire:click="toggleFavorite({{ $response->id }})" class="grid h-11 w-11 shrink-0 place-items-center rounded-lg border border-slate-200 text-lg">{{ $response->is_favorite ? '★' : '☆' }}</button></div><div class="mt-4 flex flex-wrap gap-2"><button type="button" x-data x-on:click="navigator.clipboard.writeText(@js($response->content)); $el.textContent='Copiado!'; setTimeout(() => $el.textContent='Copiar resposta',1200)" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Copiar resposta</button><button wire:click="editResponse({{ $response->id }})" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm">Editar</button><button wire:click="deleteResponse({{ $response->id }})" wire:confirm="Tem certeza que deseja excluir esta resposta?" class="rounded-lg border border-red-200 px-4 py-2.5 text-sm text-red-600">Excluir</button></div></article>
            @empty<div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Nenhuma resposta encontrada.</div>@endforelse
        </div>
    </section>
</div>
