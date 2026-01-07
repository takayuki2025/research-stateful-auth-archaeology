<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth'; 

const authStore = useAuthStore();

useHead({
  title: 'Laravel & Nuxt 認証連携',
});

// ★★★ 修正ポイント: onMounted に戻し、サーバー実行を避ける ★★★
onMounted(async () => {
    console.log("[App.vue] App mounted. Starting client-side initAuth.");
    // Firebase Authが利用可能な環境（クライアント）でのみ実行される
    // initAuthはPromiseを返すため、awaitして解決を待ちます。
    await authStore.initAuth(); 
    console.log("[App.vue] Auth initialization resolved.");
});
</script>

<template>
  <div class="relative min-h-screen">
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>

    <!-- ローディングオーバーレイは、isAuthResolvedがtrueになるまで表示される -->
    <Transition name="fade">
      <div 
        v-if="!authStore.isAuthResolved"
        class="fixed inset-0 flex items-center justify-center bg-gray-50 bg-opacity-95 z-[9999] transition-opacity duration-300 backdrop-blur-sm"
      >
        <div class="text-center p-8 bg-white shadow-2xl rounded-xl border border-blue-200 transform scale-105">
          <!-- スピナーのSVG (Tailwind CSSを使用) -->
          <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="text-lg font-bold text-blue-600">認証情報を確認中...</p>
          <p class="text-sm text-gray-500 mt-1">初期設定と認証状態の解決を待っています</p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style>
/* Nuxtの基本的なスタイルリセットやフォント設定などをここに含める */
body {
  font-family: 'Inter', sans-serif;
}

/* フェードトランジションの定義 */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>