<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('stored_payment_methods', function (Blueprint $table) {
            // card / applepay / googlepay / konbini（将来）
            $table->string('source', 32)->default('card')->index()->after('provider_payment_method_id');
        });
    }

    public function down(): void
    {
        Schema::table('stored_payment_methods', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn('source');
        });
    }
};