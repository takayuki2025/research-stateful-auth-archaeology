<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('fee_models', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id')->nullable()->index();

            $table->unsignedBigInteger('provider_route_id')->index();

            // percentage / fixed / tiered / mixed
            $table->string('fee_type', 16);

            // e.g. 360 => 3.60%
            $table->unsignedInteger('rate_bps')->nullable();

            // fixed fee: smallest unit
            $table->unsignedInteger('fixed_amount')->nullable();
            $table->char('fixed_currency', 3)->nullable();

            // tiers for tiered/mixed
            $table->json('tiers_json')->nullable();

            // effective window
            $table->dateTime('effective_from')->nullable()->index();
            $table->dateTime('effective_to')->nullable();

            // future link to evidence/extraction, or catalog_sources ref
            $table->string('source_ref', 128)->nullable();

            $table->timestamps();

            $table->foreign('provider_route_id')
                ->references('id')
                ->on('provider_routes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_models');
    }
};