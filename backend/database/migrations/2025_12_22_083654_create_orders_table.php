<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // --- (E1) 識別子 ---
            // 内部管理用のIDとは別に、ユーザーやサポートに伝える「注文番号」
            $table->string('order_number', 64)->unique()->index()->comment('公開用注文番号');

            // --- 関連 (Relations) ---
            $table->unsignedBigInteger('shop_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            // --- 状態 (Status) ---
            $table->string('status', 50)->index()->comment('注文ステータス');

            // --- 金額 (Monetary) ---
            $table->unsignedInteger('total_amount')->comment('合計金額（最小単位）');
            $table->string('currency', 10)->default('JPY');

            // --- スナップショット (Snapshots) ---
            // 注文時の情報を固定して保存（マスター変更の影響を受けないため）
            $table->json('items_snapshot')->comment('注文時の商品詳細');
            $table->json('address_snapshot')->nullable()->comment('配送先住所の控え');

            // --- (E2, E3) 業務ライフサイクル（監査証跡） ---
            // 「いつ何が起きたか」を記録し、v2台帳の整合性を保証する
            $table->timestamp('placed_at')->nullable()->index()->comment('注文確定時刻');
            $table->timestamp('address_confirmed_at')->nullable()->comment('住所確定時刻');
            $table->timestamp('paid_at')->nullable()->index()->comment('支払い完了時刻');
            $table->timestamp('cancelled_at')->nullable()->index()->comment('キャンセル時刻');
            $table->timestamp('refunded_at')->nullable()->index()->comment('返金完了時刻');

            // --- (E4) 並行実行制御 ---
            // 同時更新による「二重決済」や「不整合」を防ぐ高度な守り
            // $table->unsignedInteger('version')->default(1)->comment('楽観的ロック用バージョン');

            // --- その他 ---
            $table->json('meta')->nullable()->comment('拡張用メタデータ');
            $table->timestamps(); // created_at (データ作成), updated_at (最終更新)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};