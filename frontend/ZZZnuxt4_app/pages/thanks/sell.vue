<template>
  <!-- Main container, centered on the screen -->
  <div class="flex items-center justify-center min-h-screen bg-gray-50">
    <!-- Card container with shadow and rounded corners -->
    <div class="container mx-auto p-8 bg-white rounded-xl shadow-2xl max-w-lg text-center transform transition duration-500 hover:scale-[1.01] hover:shadow-3xl">
      
      <!-- Confirmation Icon Section -->
      <div class="text-purple-600 mb-6">
        <!-- SVG Checkmark Icon -->
        <svg class="h-24 w-24 mx-auto animate-pulse-slow" fill="currentColor" stroke="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>

      <!-- Title and Message -->
      <h1 class="text-4xl font-extrabold mb-4 text-gray-900">
        ご出品ありがとうございます
      </h1>
      <p class="text-lg text-gray-600 mb-10">
        お客様の出品手続きは正常に完了しました。<br class="sm:hidden" />
        商品がすぐに公開されるよう処理中です。
      </p>

      <!-- Action Button (NuxtLink for routing) -->
      <!-- 'to'属性はNuxtのルーティングパスに合わせて適宜変更してください -->
      <NuxtLink to="/" class="inline-block bg-blue-600 text-white font-bold py-3 px-10 rounded-full shadow-lg hover:bg-blue-700 transition-colors duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-opacity-50">
        トップページに戻る
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
// Nuxt 3/4 では `<script setup>` を使用し、コンポーネント名などは自動的に推論されます。
// 特にロジックがないため、空の setup ブロックで十分です。

// NuxtLink を使用するため、インポートは不要です（グローバルコンポーネントとして利用可能）。
</script>

<style scoped>
/* スコープ付きCSSで特定のスタイルを追加 */
.bg-gray-50 {
    background-color: #f9fafb; /* 非常に明るいグレー */
}

/* アニメーションの定義 */
@keyframes pulse-slow {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}

/* アイコンにアニメーションを適用 */
.animate-pulse-slow {
    animation: pulse-slow 3s infinite ease-in-out;
}
</style>