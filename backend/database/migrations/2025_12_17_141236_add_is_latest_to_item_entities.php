<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // public function up(): void
    // {
    //     Schema::table('item_entities', function (Blueprint $table) {
    //         $table->boolean('is_latest')
    //             ->default(true)
    //             ->after('extensions');

    //         $table->index(['item_id', 'entity_type', 'is_latest']);
    //     });
    // }

    // public function down(): void
    // {
    //     Schema::table('item_entities', function (Blueprint $table) {
    //         $table->dropIndex(['item_id', 'entity_type', 'is_latest']);
    //         $table->dropColumn('is_latest');
    //     });
    // }
};
