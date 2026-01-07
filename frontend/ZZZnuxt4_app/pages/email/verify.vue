<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useNuxtApp, navigateTo } from '#app';
// 💡 修正: '#imports' での自動インポートが失敗しているため、
// 💡 相対パスまたはエイリアスを使用して明示的にインポートします。
import { useAuthStore } from '../stores/auth'; 

// レイアウトを 'auth' に指定
definePageMeta({
  // middleware: 'auth',
  layout: 'auth', 
  // 💡 競合を防ぐため、ミドルウェアの定義を削除しました。
  // 認証チェックは onMounted フック内で手動で行います。
});

const authStore = useAuthStore();
const statusMessage = ref<string | null>(null);
const resendLoading = ref(false);

// ----------------------------------------------------
// 💡 認証状態のチェックとリダイレクト (重要)
// ----------------------------------------------------
onMounted(() => {
  const user = authStore.user;

  if (!user) {
    // ユーザーがログインしていない場合は、ログインページに移動させる
    // 登録直後の場合は必ず user があるはずなので、これは主に直接アクセスされた場合の対応です。
    return navigateTo('/login');
  }

  // ユーザーが既にメール認証済みの場合、メインページにリダイレクトする
  if (user.email_verified_at) {
    // 💡 注意: 実際のバックエンドのユーザーオブジェクト構造を確認し、
    // email_verified_at などの検証済みフラグに合わせてください。
    return navigateTo('/');
  }
});
// ----------------------------------------------------

const handleResend = async () => {
  resendLoading.value = true;
  statusMessage.value = null;

  try {
    // バックエンドのメール再送APIを叩く
    await useNuxtApp().$fetch('/api/email/verification-notification', { 
      method: 'POST' 
    });
    
    statusMessage.value = '新しい認証リンクが、あなたのメールアドレスに送信されました。';

  } catch (error) {
    console.error("Resend verification failed:", error);
    statusMessage.value = '認証メールの再送に失敗しました。しばらくしてからお試しください。';
  } finally {
    resendLoading.value = false;
  }
};
</script>

<template>
  <!-- Verification Box: Login/Registerと同じく、中央寄せのカードスタイルを適用 -->
  <div class="w-full max-w-xl p-8 bg-white rounded-lg shadow-xl text-center">
    
    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">
      メール認証のお願い
    </h2>

    <div class="space-y-4 text-gray-700">
      <p class="text-lg">
        ご登録ありがとうございます。
      </p>
      <p>
        お客様のメールアドレス（<span class="font-semibold text-blue-600">{{ authStore.user?.email || 'N/A' }}</span>）宛に認証メールを送付しました。
      </p>
      <p>
        メールに記載されたリンクをクリックして、認証を完了してください。
      </p>
    </div>

    <!-- Status Message -->
    <div v-if="statusMessage" class="mt-6 p-3 bg-green-100 text-green-700 rounded text-sm font-medium">
      {{ statusMessage }}
    </div>

    <!-- MailHog Link (開発環境用) -->
    <div class="mt-8">
      <a href="http://localhost:8025" target="_blank" class="inline-block px-6 py-2 bg-yellow-500 text-white rounded-md font-semibold hover:bg-yellow-600 transition duration-150 shadow-md">
        開発用: メールボックスを確認 (MailHog)
      </a>
    </div>

    <!-- Resend Button -->
    <form @submit.prevent="handleResend" class="mt-6">
      <button 
        type="submit" 
        class="w-full bg-blue-600 text-white py-2 rounded-md font-semibold hover:bg-blue-700 transition duration-150 shadow-lg disabled:bg-gray-400" 
        :disabled="resendLoading"
      >
        {{ resendLoading ? '送信中...' : '認証メールを再送する' }}
      </button>
    </form>
    
  </div>
</template>

<style scoped>
/* Tailwind CSS を使用しているため、ここではカスタムスタイルを最小限に抑えます */
</style>