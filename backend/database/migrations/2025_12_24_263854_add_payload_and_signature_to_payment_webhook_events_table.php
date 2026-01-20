<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payment_webhook_events', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('payload_hash');
            $table->string('signature', 512)->nullable()->after('payload'); // 任意：Stripe-Signature を監査用に残す
        });
    }

    public function down(): void
    {
        Schema::table('payment_webhook_events', function (Blueprint $table) {
            $table->dropColumn(['payload', 'signature']);
        });
    }
};