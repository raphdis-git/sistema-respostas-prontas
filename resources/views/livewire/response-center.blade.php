<div>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Respostas prontas</h1>
            <p class="mt-1 text-sm text-slate-500">Selecione uma categoria ou pesquise diretamente.</p>
        </div>
        <button type="button" class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">+ Nova resposta</button>
    </header>

    <div class="mt-6">
        <input wire:model.live.debounce.250ms="search" type="search" placeholder="Buscar por título, conteúdo ou palavra-chave..." class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3.5 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
    </div>

    <section class="mt-7">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-semibold">Categorias</h2>
            <button wire:click="selectCategory(null)" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Todas</button>
        </div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($categories as $category)
                <button wire:click="selectCategory({{ $category->id }})" class="min-h-20 rounded-xl border p-4 text-left transition {{ $categoryId === $category->id ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-100' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                    <span class="block font-semibold leading-5">{{ $category->name }}</span>
                    <span class="mt-1.5 block text-xs text-slate-500">{{ $category->responses_count }} {{ $category->responses_count === 1 ? 'resposta' : 'respostas' }}</span>
                </button>
            @endforeach
        </div>
    </section>

    <section class="mt-8">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-semibold">{{ $categoryId ? optional($categories->firstWhere('id', $categoryId))->name : ($favoritesOnly ? 'Favoritos' : 'Todas as respostas') }}</h2>
                <p class="text-sm text-slate-500">{{ $responses->count() }} {{ $responses->count() === 1 ? 'resposta encontrada' : 'respostas encontradas' }}</p>
            </div>
            <button wire:click="toggleFavorites" class="rounded-lg border px-3 py-2 text-sm {{ $favoritesOnly ? 'border-amber-300 bg-amber-50 text-amber-800' : 'border-slate-300 bg-white' }}">★ Favoritos</button>
        </div>

        <div class="space-y-3">
            @forelse($responses as $response)
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            @if($response->category)<div class="text-xs font-medium text-slate-500">{{ $response->category->name }}</div>@endif
                            <h3 class="mt-1 text-lg font-semibold">{{ $response->title }}</h3>
                            <div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $response->content }}</div>
                            @if($response->keywords)
                                <div class="mt-3 flex flex-wrap gap-1.5">@foreach($response->keywords as $keyword)<span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-500">#{{ $keyword }}</span>@endforeach</div>
                            @endif
                        </div>
                        <button wire:click="toggleFavorite({{ $response->id }})" class="grid h-11 w-11 shrink-0 place-items-center rounded-lg border border-slate-200 text-lg" title="Favoritar">{{ $response->is_favorite ? '★' : '☆' }}</button>
                    </div>
                    <div class="mt-4">
                        <button type="button" x-data x-on:click="navigator.clipboard.writeText(@js($response->content)); $el.textContent='Copiado!'; setTimeout(() => $el.textContent='Copiar resposta', 1200)" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Copiar resposta</button>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Nenhuma resposta encontrada.</div>
            @endforelse
        </div>
    </section>
</div>
