<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('words', function (Blueprint $table): void {
            $table->id();
            $table->string('original');
            $table->string('translated');
            $table->dateTime('done_at')->nullable();
            $table->boolean('starred')->default(false);
            $table->foreignIdFor(User::class);
            $table->string('language', 5);
            $table->integer('views')->default(0);
            $table->json('example_original')->nullable();
            $table->json('example_translated')->nullable();
            $table->boolean('from_sample')->default(false);
            $table->integer('shared_by')->nullable();
            $table->index('language');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
