# Sistema de Respostas Prontas

Central web para organizar, pesquisar e copiar respostas prontas de suporte técnico.

## Stack
- Laravel 12
- Livewire 3
- Tailwind CSS 4
- SQLite por padrão (pode ser trocado por MySQL/PostgreSQL)

## Primeira versão
- Categorias com nomes longos
- Busca por título, conteúdo e palavras-chave
- Favoritos
- Cadastro e edição de respostas
- Botão para copiar resposta
- Estrutura preparada para importação de arquivos Word

## Instalação
```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Acesse `http://127.0.0.1:8000`.
