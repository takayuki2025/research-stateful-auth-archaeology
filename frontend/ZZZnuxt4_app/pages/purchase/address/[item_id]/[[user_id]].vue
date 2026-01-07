<script setup lang="ts">
import { ref, watch } from 'vue';
import { useAuthStore } from '@/stores/auth'; 
import { useRoute, useRouter } from 'vue-router'; 
import { storeToRefs } from 'pinia';
import { useApi } from '~/composables/useApi'; 

// ==========================
// 型定義
// ==========================
interface AddressForm {
  post_number: string | null;
  address: string | null;
  building: string | null;
}

interface User {
    id: number;
    name: string;
    email: string;
    uid: string;
    post_number: string | null;
    address: string | null;
    building: string | null;
    user_image?: string | null;
}
// ----------------

// --- 初期化と状態管理 ---
const router = useRouter();
const route = useRoute(); 
const { authenticatedFetch } = useApi(); 

const authStore = useAuthStore();
const { user: authUser, isAuthenticated } = storeToRefs(authStore); 

const isLoading = ref(true);
const isSubmitting = ref(false);
const serverErrors = ref<{ [key: string]: string }>({});
const successMessage = ref('');
const errorMessage = ref(''); 

// URLから取得したIDを保持するRef
const itemId = ref<string>('');
const userIdFromRoute = ref<string>('');

// フォームデータ
const form = ref<AddressForm>({
  post_number: null,
  address: null,
  building: null,
});

/**
 * フォームの状態をPiniaストアまたはAPIデータで初期化する
 * @param data 初期化に使用するユーザーデータ（API応答またはPiniaストア）
 */
const initializeForm = (data: User | null) => {
    if (data) {
        form.value.post_number = data.post_number || '';
        form.value.address = data.address || '';
        form.value.building = data.building || '';
    }
}

/**
 * Nuxt/Vue Routerの動的ルートパラメータから単一の値を抽出
 * @param param ルートパラメータの値 (string, string[], または undefined)
 * @returns 抽出された値 (string)
 */
const getIdFromParam = (param: string | string[] | undefined): string => {
    if (!param) return '';
    // 配列の場合は最初の要素を、それ以外はそのまま返す。ただし、null/undefinedは空文字として扱う。
    if (Array.isArray(param)) return param[0] || '';
    return param as string || '';
}


/**
 * APIから現在の住所情報を取得し、フォームにセットする
 * IDはitemId.valueとuserIdFromRoute.valueから取得する。
 */
const fetchCurrentAddress = async () => {
    // 実行時にRefから確定したIDを取得
    const pItemId = itemId.value;
    const pUserId = userIdFromRoute.value;
    
    isLoading.value = true;
    serverErrors.value = {};
    errorMessage.value = '';

    // IDが揃っていない場合はwatch側でエラー処理されるため、ここでは警告のみ
    if (!pItemId || !pUserId) {
        console.warn('fetchCurrentAddressがID無しで呼び出されました。実行をスキップします。');
        isLoading.value = false;
        return;
    }

    // 1. 認証状態の解決を待つ
    // Piniaストアが初期化され、最新のユーザー情報が確定するのを待つ
    await authStore.waitForAuthResolution();

    // ★ 修正ポイント1: 認証チェックの厳格化
    if (!isAuthenticated.value || !authUser.value || String(authUser.value.id) !== pUserId) {
        // ユーザーが認証されていない OR Piniaにユーザーデータがない OR URLのユーザーIDとPiniaのユーザーIDが一致しない
        console.error(
            `[AUTH ERROR] isAuthenticated: ${isAuthenticated.value}, ` +
            `Store User ID: ${authUser.value?.id} (${typeof authUser.value?.id}), ` +
            `Route User ID: ${pUserId} (${typeof pUserId})`
        );
        
        // 認証されていない場合はログインページへ
        if (!isAuthenticated.value) {
            errorMessage.value = '認証されていません。ログインページへ移動します。';
            setTimeout(() => router.push('/login'), 1000);
        } else if (String(authUser.value.id) !== pUserId) {
            // 認証済みだが、URLのユーザーIDが現在ログイン中のユーザーと異なる場合
             errorMessage.value = '権限がありません。ログイン中のユーザーとURLのユーザーIDが一致しません。';
        }

        isLoading.value = false;
        return;
    }

    console.log(`[DEBUG: AddressEdit] Route Params (Confirmed) - itemId: ${pItemId}, userIdFromRoute: ${pUserId}`);
    
    // 2. Piniaストアのデータでフォームを初期化（APIコール失敗時のフォールバック）
    initializeForm(authUser.value as User | null);
    
    // 3. APIエンドポイントの構築
    // URL例: /purchase/address/4/10
    const url = `/purchase/address/${pItemId}/${pUserId}`;
    
    console.log(`[DEBUG: AddressEdit] Attempting GET to: /api${url}`);
    
    try {
        const response: any = await authenticatedFetch(url, { method: 'GET' });

        console.log('[DEBUG: AddressEdit] API Response Data:', response);

        if (response && response.user_address) {
            // 成功: APIから返された最新の住所データをフォームにセット
            const addressData = response.user_address as User;
            initializeForm(addressData); // APIデータで上書き
            
            console.log(`✅ Form initialized by API for User ID: ${addressData.id}`);
        
        } else {
             console.warn('APIから住所データが取得できませんでした。Piniaストアのデータを使用します。');
        }
    } catch (error: any) {
        console.error('住所情報取得エラー (authenticatedFetch):', error);
        
        const statusCode = error.status || (error.response ? error.response.status : '不明');
        
        errorMessage.value = error.message || `住所情報の取得中に予期せぬエラーが発生しました (Status: ${statusCode})。`;
        
        // ★ 修正ポイント2: APIコールが401/403を返した場合、強制的にリダイレクト
        if (statusCode === 401 || statusCode === 403) {
             errorMessage.value = '認証情報が無効です。ログインページへ移動します。';
             setTimeout(() => router.push('/login'), 1000);
        }
        
    } finally {
        isLoading.value = false;
    }
};

/**
 * フォームの送信処理 (AuthStoreのアクションを通じて住所を更新)
 */
const submitAddressUpdate = async () => {
  if (isSubmitting.value || isLoading.value) return;

  isSubmitting.value = true;
  serverErrors.value = {};
  successMessage.value = '';
  errorMessage.value = ''; 

  try {
    await authStore.waitForAuthResolution();
    // ここでも再度認証チェックを行う
    if (!authStore.isAuthenticated) {
        errorMessage.value = 'セッションが切れました。再度ログインが必要です。';
        setTimeout(() => router.push('/login'), 1500);
        return;
    }
    
    console.log(`[DEBUG: AddressEdit] Attempting PUT to: /api/user/update with data:`, form.value);
    
    const responseData: any = await authenticatedFetch('/user/update', {
      method: 'PUT',
      body: form.value,
    });

    console.log('[DEBUG: AddressEdit] Update API Response Data:', responseData);

    if (responseData && responseData.user) {
        // Piniaストアを更新
        authStore.$patch({ user: responseData.user });
    } else {
        console.warn('API応答に更新されたユーザーオブジェクト (user) が含まれていませんでした。');
        // フォールバックとしてフォームの内容で部分的にストアを更新
        authStore.$patch({ user: { ...authUser.value, ...form.value } });
    }
    
    successMessage.value = '住所情報を正常に更新しました！';

    // 🔹 ページ遷移: /purchase/address/{item_id} に戻る
    setTimeout(() => {
        if(itemId.value) {
            router.push(`/purchase/address/${itemId.value}`); 
        } else {
            // item_idがない場合は安全のためトップに戻る
            router.push('/'); 
        }
    }, 1500);

  } catch (error: any) {
    console.error('住所更新エラー:', error);
    
    const statusCode = error.status || (error.response ? error.response.status : '不明');
    
    // ★ 修正ポイント2: 送信時にも401/403をチェック
    if (statusCode === 401 || statusCode === 403) {
        errorMessage.value = 'セッションが無効です。再度ログインが必要です。';
        setTimeout(() => router.push('/login'), 1500);
        return;
    }
    
    if (statusCode === 422) {
      serverErrors.value = error.response._data.errors || {};
      errorMessage.value = '入力内容に誤りがあります。ご確認ください。';
    } else {
      errorMessage.value = error.message || `住所更新中に予期せぬエラーが発生しました (Status: ${statusCode})。`;
    }
    
  } finally {
    isSubmitting.value = false;
  }
};


// ==========================
// ライフサイクル & ウォッチ (パラメータの確定を監視)
// ==========================

// ★ 修正ポイント3: Piniaストアのユーザー情報変更を監視し、フェッチをトリガーする
watch(authUser, (newUser, oldUser) => {
    // ユーザーが変わった（IDが異なる、または古いユーザーがいたが新しいユーザーがいなくなった）場合
    // 新規ログイン/ログアウトを検知
    const userIdChanged = (!!newUser && !!oldUser && newUser.id !== oldUser.id) || (!newUser !== !oldUser);

    if (userIdChanged) {
         console.log('--- WATCH: Pinia user state changed (New Login/Logout). Re-fetching data. ---');
         // item_idとuser_idが揃っているか確認してフェッチを再実行
         if (itemId.value && userIdFromRoute.value) {
            fetchCurrentAddress(); 
         }
    }
});


watch(() => route.params, (newParams) => {
    
    // 💡 デバッグログを追加
    console.log('--- WATCH: route.params update detected (Initial Load/Navigation) ---');
    console.log('New Params Object:', JSON.stringify(newParams));
    
    // 💥 修正ポイント: 'item_id' キーを優先して取得
    const params = newParams as any; // 型エラー回避のため一時的にanyにキャスト
    const pItemId = getIdFromParam(params.item_id || params.id); // item_idを優先し、idをフォールバックとして使用
    const pUserId = getIdFromParam(params.user_id);
    
    console.log(`Extracted IDs - Item ID: "${pItemId}", User ID: "${pUserId}"`);

    // Ref変数を更新
    itemId.value = pItemId;
    userIdFromRoute.value = pUserId;
    
    // エラーメッセージをリセットして再ロードに備える
    errorMessage.value = '';
    
    // ルートパラメータが変更されたとき、かつ必要なIDが揃っているとき
    if (pItemId && pUserId) {
        // パラメータが揃ったらフェッチをトリガー
        console.log('✅ Both IDs available. Triggering fetchCurrentAddress.');
        fetchCurrentAddress(); 
    } else {
        // パラメータが一つでも欠けている場合はエラーを表示し、ローディングを確実に終了
        console.log('❌ Missing required IDs. Displaying error message.');
        isLoading.value = false;
        errorMessage.value = 'URLから商品ID（item_id）またはユーザーIDが取得できませんでした。ページURLをご確認ください。';
    }
}, { 
    immediate: true, // コンポーネントがマウントされた直後に一度実行
    deep: true // route.paramsオブジェクトの内容の変更を深く監視
}); 
</script>

<template>
  <div class="max-w-3xl mx-auto py-16 px-4">
    <h1 class="text-3xl font-extrabold mb-8 text-center text-gray-800 border-b pb-2">配送先住所の変更</h1>

    <!-- ローディング -->
    <div v-if="isLoading" class="text-center py-10">
      <div class="animate-spin rounded-full h-8 w-8 border-b-4 border-indigo-600 inline-block mr-2"></div>
      <p class="text-indigo-600 font-semibold mt-2">住所情報を読み込み中...</p>
    </div>

    <!-- エラー (errorMessageの定義は維持) -->
    <div
      v-else-if="errorMessage"
      class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow-md"
    >
      <p class="font-bold">エラーが発生しました</p>
      <p class="mt-1 text-sm">{{ errorMessage }}</p>
      <!-- ユーザー向けのURLガイダンス -->
       <div v-if="errorMessage.includes('商品ID') || errorMessage.includes('ユーザーID')">
            <p class="mt-3 text-xs font-semibold text-red-800">💡 URLの確認をお願いします</p>
            <p class="text-xs mt-1">このページは **商品ID** と **ユーザーID** が必要です。</p>
            <p class="text-xs mt-1">期待されるURLの例: <code class="bg-red-200 p-1 rounded">/purchase/address/4/10</code></p>
        </div>
    </div>

    <!-- 成功 -->
    <div
      v-if="successMessage"
      class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-md"
    >
      <p class="font-bold">更新成功</p>
      <p class="mt-1 text-sm">{{ successMessage }}</p>
    </div>

    <!-- フォーム -->
    <form
      v-else
      @submit.prevent="submitAddressUpdate"
      class="space-y-6 bg-white p-8 rounded-xl shadow-2xl border border-gray-100"
    >
      <!-- デバッグ情報: ユーザーに見えるようにURLパラメータを表示 -->
      <div class="text-xs text-gray-500 mb-2 border-b pb-2">
            URLから取得したID (確認用): 
            <span class="font-mono bg-gray-100 p-1 rounded">
                商品ID: {{ itemId || '不明' }} / ユーザーID: {{ userIdFromRoute || '不明' }}
            </span>
      </div>
      
      <div v-if="authUser" class="text-sm text-gray-600 mb-4 border-b pb-4">
        <p class="font-semibold">現在のユーザー情報 (Pinia Storeから):</p>
        <p>名前: {{ authUser.name }}</p>
        <p>メール: {{ authUser.email }}</p>
      </div>

      <!-- 郵便番号 -->
      <div>
        <label for="post_number" class="block font-semibold mb-1 text-gray-700">郵便番号</label>
        <input
          id="post_number"
          type="text"
          v-model="form.post_number"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
          :disabled="isSubmitting"
        />
        <p v-if="serverErrors.post_number" class="text-red-600 text-sm mt-1">
          {{ serverErrors.post_number }}
        </p>
      </div>

      <!-- 住所 -->
      <div>
        <label for="address" class="block font-semibold mb-1 text-gray-700">住所</label>
        <input
          id="address"
          type="text"
          v-model="form.address"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
          :disabled="isSubmitting"
        />
        <p v-if="serverErrors.address" class="text-red-600 text-sm mt-1">
          {{ serverErrors.address }}
        </p>
      </div>

      <!-- 建物名 -->
      <div>
        <label for="building" class="block font-semibold mb-1 text-gray-700">建物名（任意）</label>
        <input
          id="building"
          type="text"
          v-model="form.building"
          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150"
          :disabled="isSubmitting"
        />
        <p v-if="serverErrors.building" class="text-red-600 text-sm mt-1">
          {{ serverErrors.building }}
        </p>
      </div>

      <!-- 送信ボタン -->
      <button
        type="submit"
        :disabled="isSubmitting"
        class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 transition duration-150 shadow-lg mt-8 disabled:opacity-50"
      >
        {{ isSubmitting ? '更新中...' : '住所を更新する' }}
      </button>

       <!-- 戻るボタン -->
        <div class="mt-4 text-center">
            <button 
                @click="router.push(`/purchase/address/${itemId}`)"
                type="button"
                class="text-sm font-medium text-gray-600 hover:text-gray-800 transition duration-150"
                :disabled="isSubmitting"
            >
                キャンセルして前の画面に戻る
            </button>
        </div>
    </form>
  </div>
</template>