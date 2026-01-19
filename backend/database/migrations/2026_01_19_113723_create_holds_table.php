<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('account_id')->index();

            $table->bigInteger('amount'); // 正の値
            $table->string('currency', 10)->default('JPY');

            $table->string('reason_code', 64)->index(); // shipment_pending, chargeback_risk, manual_review...
            $table->string('status', 32)->index();      // active | released | cancelled

            $table->json('meta')->nullable();

            $table->dateTime('held_at')->index();
            $table->dateTime('released_at')->nullable()->index();

            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holds');
    }
};