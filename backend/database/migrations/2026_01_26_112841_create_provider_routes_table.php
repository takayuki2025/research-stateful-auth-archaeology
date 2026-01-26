<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('provider_routes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id')->nullable()->index();

            $table->unsignedBigInteger('provider_id');

            // per-provider unique key: jp_jpy_card, global_usd_card, etc.
            $table->string('route_key', 64);

            // ISO 3166-1 alpha-2, ISO 4217
            $table->char('country', 2)->index();
            $table->char('currency', 3)->index();

            // card / bank_transfer / apple_pay / google_pay / konbini / etc.
            $table->string('payment_method', 32)->index();

            $table->string('status', 16)->default('active')->index();

            // e.g. max_amount, card_brands, business_only, etc.
            $table->json('constraints_json')->nullable();

            $table->timestamps();

            // constraints
            $table->unique(['provider_id', 'route_key'], 'uk_provider_route_key');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_routes');
    }
};