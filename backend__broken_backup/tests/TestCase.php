<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Contracts\Hashing\Hasher; 

abstract class TestCase extends BaseTestCase
{
    // DBをリフレッシュするトレイトを使用
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 🌟 ハッシュをモックに置き換え、常に検証をパスさせる 🌟
        $mockHasher = new class implements \Illuminate\Contracts\Hashing\Hasher {
            public function make($value, array $options = []) { return $value; }
            public function check($value, $hashedValue, array $options = []) { return true; }
            public function needsRehash($hashedValue, array $options = []) { return false; }
            // 修正: 'algo'キーを含めることで、Laravel 11のHashManager::isHashed()のエラーを回避
            public function info($hashedValue) { return ['mocked' => true, 'algo' => 'mock']; }
            public function isHashed($value) { return true; }
            public function verifyConfiguration($hashedValue): bool { return true; } 
        };

        // 1. Hasherのインターフェースをモックインスタンスにバインド
        $this->app->instance(\Illuminate\Contracts\Hashing\Hasher::class, $mockHasher);

        // 2. Hashファサードが参照するHashManagerにも直接モックドライバを設定
        $this->app->forgetInstance('hash'); // 既存のハッシュインスタンスを破棄
        $this->app->singleton('hash', function ($app) use ($mockHasher) {
            $manager = new \Illuminate\Hashing\HashManager($app);
            // 'mock'ドライバを登録
            $manager->extend('mock', fn() => $mockHasher);
            // テスト中はハッシュドライバを'mock'に強制設定
            $app['config']->set('hashing.driver', 'mock');
            return $manager;
        });
    }
}