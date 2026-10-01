<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saved_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->json('keywords')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->string('source')->nullable();
            $table->timestamps();
            $table->index(['category_id', 'is_favorite']);
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_responses');
    }
};
