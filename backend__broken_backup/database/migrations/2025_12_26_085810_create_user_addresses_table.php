<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('post_number', 20);
            $table->string('prefecture', 50);
            $table->string('city', 100);
            $table->string('address_line1', 255);
            $table->string('address_line2')->nullable();

            $table->string('recipient_name', 100);
            $table->string('phone', 30)->nullable();

            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
