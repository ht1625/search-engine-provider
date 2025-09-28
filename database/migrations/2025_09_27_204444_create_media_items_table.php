<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->json('stats')->nullable();
            $table->json('tags')->nullable();
            $table->float('score')->nullable();
            $table->string('provider_name');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
