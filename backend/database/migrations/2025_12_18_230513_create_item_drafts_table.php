<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('item_drafts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /*
             |--------------------------------------------------------------------------
             | 🔑 Seller（SoT）
             |--------------------------------------------------------------------------
             | individual:6
             | shop:2
             | shop:managed
             */
            $table->string('seller_id', 50)
                ->comment('Source of Truth for seller (individual:{userId} | shop:{shopId|managed})');

            /*
             |--------------------------------------------------------------------------
             | 補助的な参照（nullable）
             |--------------------------------------------------------------------------
             | ・検索 / JOIN 最適化用
             | ・正規化は Domain で行う
             */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('shop_id')
                ->nullable()
                ->constrained('shops')
                ->nullOnDelete();

            /*
             |--------------------------------------------------------------------------
             | Item draft fields
             |--------------------------------------------------------------------------
             */
            $table->string('name', 255);
            $table->integer('price'); // JPY only (minor unit)
            $table->string('brand', 255)->nullable();

            $table->string('item_image')->nullable(); // Draft image path
            $table->string('explain')->nullable();
            $table->string('condition')->nullable();
            $table->json('category')->nullable(); // Draft raw categories
            $table->integer('remain')->default(1);

            $table->string('status', 20); // draft / published

            $table->timestamps();

            /*
             |--------------------------------------------------------------------------
             | Index（運営前なので最小限）
             |--------------------------------------------------------------------------
             */
            $table->index('seller_id');
            $table->index('user_id');
            $table->index('shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_drafts');
    }
};
