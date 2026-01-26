<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('catalog_sources', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id')->nullable()->index();

            $table->unsignedBigInteger('provider_id')->index();

            // html / pdf / manual
            $table->string('source_type', 16)->index();

            // URL can be long -> do NOT unique-index on TEXT in MySQL
            $table->text('source_url')->nullable();

            // stable unique key for URL
            $table->char('source_url_hash', 64)->index();

            // daily / weekly / monthly
            $table->string('update_frequency', 16)->default('weekly');

            // active / inactive
            $table->string('status', 16)->default('active')->index();

            $table->dateTime('last_fetched_at')->nullable();

            // diff baseline
            $table->char('last_hash', 64)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['provider_id', 'source_url_hash'], 'uk_provider_source_hash');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_sources');
    }
};