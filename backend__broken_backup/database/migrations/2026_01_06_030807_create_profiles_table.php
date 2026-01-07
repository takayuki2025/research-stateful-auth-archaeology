<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            // Auth/User の唯一の結合点
            $table->unsignedBigInteger('user_id')->unique();

            $table->string('display_name');
            $table->string('post_number')->nullable();
            $table->string('address')->nullable();
            $table->string('building')->nullable();
            $table->string('user_image')->nullable();

            $table->timestamps();

            // 🔒 User 削除時は Profile も消える
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
