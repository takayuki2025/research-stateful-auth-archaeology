<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();

            // v3.3 MultiProject: 最初は nullable で良い（後で必須化可）
            $table->unsignedBigInteger('project_id')->nullable()->index();

            // stable key: stripe, paypal, etc.
            $table->string('key', 64)->unique();

            $table->string('display_name', 128);

            // psp / bank / wallet / carrier / etc.
            $table->string('provider_type', 32)->index();

            // active / inactive
            $table->string('status', 16)->default('active')->index();

            $table->text('website_url')->nullable();
            $table->text('support_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
