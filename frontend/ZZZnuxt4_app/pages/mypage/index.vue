<script setup lang="ts">
import { ref, watch, onMounted, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRoute, navigateTo, useNuxtApp, useRuntimeConfig } from "#app";
import { storeToRefs } from "pinia";

definePageMeta({
  layout: "default",
});

const { $api } = useNuxtApp();
if (typeof $api !== 'function') {
  console.error(`CRITICAL: $api instance is missing. Check plugins/api-interceptor.ts.`);
}

const authStore = useAuthStore();
// PiniaストアからisAuthenticatedとtokenを取得
const { isAuthenticated: isAuthed, token } = storeToRefs(authStore); 
const route = useRoute();

// --- 型定義 ---
interface User {
  id: number;
  name: string;
  email: string;
  uid: string;
  email_verified_at: string | null;
  post_number: string | null;
  address: string | null;
  building: string | null;
  user_image: string | null;
}
interface Item {
  id: number;
  name: string;
  item_image: string; // 商品画像のパス
  remain: number;
  item?: Item; // 'buy'ページの場合に購入情報に含まれる商品データ
}
// --- 状態管理 ---
const user = ref<User | null>(null);
const items = ref<Item[]>([]);

const form = ref<any>({
  name: '', 
  post_number: '', 
  address: '', 
  building: '',
});

const profileErrors = ref<any>({});
const imageError = ref<string | null>(null);
const successMessage = ref<string | null>(null);
const isLoading = ref(true);

const page = computed(() => (route.query.page === 'buy' ? 'buy' : 'sell'));

// --- ユーザー情報取得（初期表示時）---
const fetchUserProfile = async () => {
  isLoading.value = true;
  await authStore.waitForAuthResolution(); // 認証ストアの解決を待つ

  // 1. 認証チェック
  // Piniaストアのユーザー情報またはトークンがない場合はログインへリダイレクト
  if (!authStore.user || !token.value) { // ★ 認証トークンもチェックに追加 ★
    console.log('ユーザー情報または認証トークンがないため、ログインへリダイレクトします。');
    await navigateTo('/login');
    isLoading.value = false;
    return;
  }
  
  // Piniaストアのユーザー情報を初期データとして設定
  user.value = authStore.user as User;

  // 2. CSRFトークンを取得 (Sanctumセッションの確立)
  try {
    // Sanctum認証がBearer認証と併用されている場合、これはセッション確立に重要
    await authStore.getSanctumCsrfToken();
  } catch(e) {
    // CSRFトークン取得失敗は、認証が切れている可能性を示唆
    console.error('CSRFトークン取得に失敗しました。認証失敗とみなし、継続またはリダイレクトを試みます。', e);
    // CSRF取得失敗でも、Bearerトークンが有効なら次のAPIコールで認証される可能性があるため、処理は継続します。
  }

  // 3. APIから最新の完全なプロフィールデータを取得
  try {
    // responseの型は { user: User } または空オブジェクトやnullを想定
    const response: { user?: User } = await $api(`mypage/profile`, {});

    if (response && response.user) {
      user.value = response.user;
    } 

    // フォームデータも更新
    form.value.name = user.value!.name || ``;
    form.value.post_number = user.value!.post_number || ``;
    form.value.address = user.value!.address || ``;
    form.value.building = user.value!.building || ``;

    // メール認証後のクエリパラメータ処理
    if (route.query.verified === `true`) {
      successMessage.value = `メール認証が完了しました！引き続きサービスをご利用いただけます。`;
      await navigateTo({ path: route.path, query: { page: page.value } }, { replace: true });
    }

  } catch (error: any) {
    // 401エラーはグローバルインターセプター (plugins/api-interceptor.client.ts) で処理されるはずです。
    // そのため、ここで手動で401チェックを入れるのは冗長になりますが、
    // 念のため、グローバルインターセプターが機能しなかった場合のエラー処理を残しておきます。
    if (error.response && error.response.status === 401) {
      console.log('401エラーを捕捉しました（fetchUserProfile）。グローバルインターセプターが作動しない場合、ここでリダイレクト。');
      await navigateTo('/login');
      return;
    }
    console.error('プロフィールデータの取得に失敗しました:', error);
    successMessage.value = 'プロフィールデータのロードに失敗しました。';
  } finally {
    isLoading.value = false;
  }
};

// --- 商品リスト取得処理 ---
const fetchItems = async () => {
  // ユーザープロフィールのロードが完了していることを確認
  // ここで user.value が null の場合、fetchUserProfile() でリダイレクトされているはず
  if (!user.value || !token.value) { // ★ トークンも再チェック
      await fetchUserProfile(); // プロフィールが未ロードならロードを試みる
  }
  if (!user.value || !token.value) return; // 認証されていない場合はここで終了

  isLoading.value = true;
  items.value = [];

  try {
    const endpoint = `mypage/items?page=${page.value}`; 
    
    // バックエンドから商品リストを取得する ($apiが自動で認証ヘッダーを付与)
    // LaravelのItemモデルのアクセサがitem_imageを絶対URLに変換して返すことを想定
    const response: { items: Item[] } = await $api(endpoint, {}); 
    items.value = response.items || [];
    
  } catch (error: any) {
    console.error(`${page.value}商品の取得に失敗しました:`, error);
    if (error.response && error.response.status === 401) { 
      // 401エラーはグローバルインターセプターが処理するはずですが、念のためログとリダイレクト
      console.log(`401エラーを捕捉しました（アイテム取得）。ログインページへリダイレクトします。`);
      await navigateTo(`/login`);
    } else {
       // 商品取得エラーは致命的ではないため、ロード状態のみ解除
    }
  } finally {
    isLoading.value = false;
  }
};

watch(page, fetchItems, { immediate: true });

onMounted(() => {
  fetchUserProfile();
});

// --- 画像アップロード処理 (省略) ---
const handleImageUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (!file || !user.value) return;

  imageError.value = null; 
  successMessage.value = null;
  isLoading.value = true;

  const formData = new FormData();
  formData.append('user_image', file);

  try {
    await authStore.getSanctumCsrfToken();

    const response: any = await $api(`upload2`, { 
      method: `POST`,
      body: formData,
    });

    user.value!.user_image = response.image_path; 
    successMessage.value = `画像をアップロードしました。`;

  } catch (error: any) {
    console.error('画像アップロードに失敗:', error);
    if (error.response && error.response.status === 422) {
      imageError.value = error.response._data.errors.user_image?.[0] || '無効なファイルです。';
    }
    else if (error.response && error.response.status === 401) {
      successMessage.value = 'セッションが切れました。再度ログインが必要です。';
      await navigateTo('/login'); 
      return;
    } else {
      imageError.value = 'アップロードに失敗しました。';
    }
  } finally {
    isLoading.value = false;
  }
};

// --- プロフィール情報更新処理 (省略) ---
const handleProfileUpdate = async () => {
  profileErrors.value = {};
  successMessage.value = null;
  if (!user.value) return;
  isLoading.value = true;

  try {
    await authStore.getSanctumCsrfToken();

    await $api(`profile_update`, { 
      method: `PATCH`,
      body: form.value,
    });

    successMessage.value = `プロフィール情報を更新しました！`;

  } catch (error: any) {
    console.error('プロフィール更新に失敗:', error);
    if (error.response && error.response.status === 422) {
      profileErrors.value = error.response._data.errors;
    } else if (error.response && error.response.status === 401) {
      successMessage.value = 'セッションが切れました。再度ログインが必要です。';
      await navigateTo('/login');
      return;
    } else {
      successMessage.value = '更新に失敗しました。再度お試しください。';
    }
  } finally {
    isLoading.value = false;
  }
};

// --- 汎用アセットURL生成ヘルパー関数 (修正箇所) ---
const getAssetUrl = (path: string | undefined | null, isProfileImage: boolean = false) => {
  // 1. path が存在しない、または空の場合は、デフォルト画像を返す
  if (!path) {
    if (isProfileImage) {
      const config = useRuntimeConfig().public;
      let base = config.apiBaseUrl;
      // LaravelのルートURLを取得するために、末尾の '/api' を取り除く
      if (base.endsWith('/api')) {
        base = base.substring(0, base.length - 4);
      }
      const DEFAULT_IMAGE_PATH = 'storage/images/default-profile2.jpg';
      // 確実にベースURLを付与してデフォルト画像パスを生成
      return `${base.replace(/\/$/, '')}/${DEFAULT_IMAGE_PATH}`;
    }
    // 商品画像の場合はパスがないので空文字列を返す
    return '';
  }

  // 2. pathがURL形式（http:// または https:// で始まる）であれば、そのまま返す
  // これにより、バックエンドで絶対パスに変換されている場合はそのまま利用
  const isAbsoluteUrl = /^https?:\/\//i.test(path);
  if (isAbsoluteUrl) {
    return path;
  }
  
  // 3. パスが絶対URL形式でなく、/storage/などで始まっている場合
  // 開発環境によってはベースURLを付与してURLを完成させる必要がある
  
  const config = useRuntimeConfig().public;
  let base = config.apiBaseUrl;
  
  // LaravelのルートURLを取得するために、末尾の '/api' を取り除く
  if (base.endsWith('/api')) {
    base = base.substring(0, base.length - 4);
  }
  
  // ベースURLとパスを結合する（重複スラッシュを避ける）
  const cleanBase = base.replace(/\/$/, '');
  const cleanPath = path.startsWith('/') ? path.substring(1) : path;
  
  return `${cleanBase}/${cleanPath}`;
};

// ユーティリティ: プロフィール編集ページへ遷移
const goToProfileEdit = () => {
  navigateTo('/mypage/profile');
};
</script>

<template>
<div v-if="isLoading" class="flex justify-center items-center h-screen">
<div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-red-500"></div>
<p class="ml-3 text-gray-600">データを読み込み中...</p>
</div>

<div v-if="successMessage" class="validation-errors bg-green-100 border border-green-400 text-green-700">
{{ successMessage }}
</div>

<div v-if="user && !isLoading" class="profile_page">
<div class="profile_header">
<div class="profile_header_1">
<!-- プロフィール画像にはデフォルト画像が必要なので isProfileImage=true を渡す -->
<img :src="getAssetUrl(user.user_image, true)" alt="プロフィール画像" class="user_image_css">
<h2 class="user_name_css">{{ user.name }}</h2>

        <div class="user_edit_css1">
            <button @click="goToProfileEdit" class="user_edit_css2">
                プロフィールを編集
            </button>
        </div>
    </div>
    
    <div class="profile_header_2">
        <NuxtLink :to="{ path: '/mypage', query: { page: 'sell' } }" 
                  class="sell_items" 
                  :class="{ 'active': page === 'sell' }">
            出品した商品
        </NuxtLink>
        <NuxtLink :to="{ path: '/mypage', query: { page: 'buy' } }" 
                  class="buy_items" 
                  :class="{ 'active': page === 'buy' }">
            購入した商品
        </NuxtLink>
    </div>
</div>

<div class="profile_content">
    <div v-if="items.length === 0" class="mt-8 text-center text-gray-500">
        <p>{{ page === "sell" ? "出品した商品はありません。" : "購入した商品はありません。" }}</p>
    </div>
    <div v-else class="items_select">
        <div v-for="item in items" :key="item.id" class="items_select_all">
            
            <NuxtLink v-if="page === 'sell'" :to="`/item/${item.id}`" class="mypage_item_">
                <!-- item.item_image が存在する場合のみ画像を表示し、修正された getAssetUrl を使用 -->
                <img v-if="item.item_image" :src="getAssetUrl(item.item_image)" :alt="item.name + 'の商品写真'">
                <div v-else class="no-image-placeholder">No Image</div>
                <div class="item-details">
                    <label>{{ item.name }}</label>
                    <span v-if="item.remain === 0" class="sold-text">sold</span>
                </div>
            </NuxtLink>
            
            <NuxtLink v-else-if="page === 'buy' && item.item" :to="`/item/${item.item.id}`" class="mypage_item_">
                <!-- item.item.item_image が存在する場合のみ画像を表示し、修正された getAssetUrl を使用 -->
                <img v-if="item.item.item_image" :src="getAssetUrl(item.item.item_image)" :alt="item.item.name + 'の商品写真'">
                <div v-else class="no-image-placeholder">No Image</div>
                <div class="item-details">
                    <label>{{ item.item.name }}</label>
                    <span v-if="item.item.remain === 0" class="sold-text">sold</span>
                </div>
            </NuxtLink>
        </div>
    </div>
</div>


</div>
</template>

<style scoped>
/* スタイル部分は修正の必要がなかったため、そのまま残します。 */

.profile_page {
margin: 0 auto;
max-width: 1400px;
}

.profile_header {
border-bottom: 2px solid #5f5f5f;
padding-bottom: 20px;
}

.user_image_css {
position: relative;
left: 200px;
width: 90px;
height: 90px;
border-radius: 50%;
overflow: hidden;
object-fit: cover;
object-position: center;
}

.user_name_css {
position: relative;
left: 220px;
}

.user_edit_css1 {
margin-left: auto;
}

.user_edit_css2 {
position: relative;
right: 200px;
width: 200px;
height: 35px;
font-weight: bold;
font-size: 15px;
color: #ff5555;
border: 2px solid #ff5555;
background-color: white;
border-radius: 5px;
cursor: pointer;
transition: background-color 0.2s, color 0.2s;
}

.user_edit_css2:hover {
background-color: #ffeaea;
}

.items_select {
display: grid;
grid-template-columns: repeat(4, 1fr);
gap: 30px;
padding: 60px;
}

.items_select_all {
width: 100%;
max-width: 250px;
display: flex;
flex-direction: column;
}

/* NuxtLink は最終的に a タグになるため、a タグセレクタを使用 */
.items_select_all a, .mypage_item_ {
display: block;
width: 100%;
height: auto;
text-decoration: none;
color: black;
transition: opacity 0.2s;
}

.items_select_all a:hover {
opacity: 0.8;
}

.items_select img {
width: 100%;
aspect-ratio: 1 / 1;
object-fit: cover;
display: block;
}

/* 画像がない場合のプレースホルダーのスタイル */
.no-image-placeholder {
  width: 100%;
  aspect-ratio: 1 / 1;
  background-color: #f0f0f0;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #a0a0a0;
  font-size: 16px;
  border: 1px dashed #ccc;
}

.item-details {
display: flex;
justify-content: space-between;
align-items: center;
gap: 8px;
margin-top: 8px;
}

.items_select_all label {
font-size: 14px;
line-height: 1.4;
overflow: hidden;
text-overflow: ellipsis;
white-space: nowrap;
}

.sold-text {
font-size: 14px;
color: #ff4041;
font-weight: bold;
white-space: nowrap;
}

.profile_header_1 {
display: flex;
height: 200px;
text-align: center;
align-items: center;
position: relative;
}

.profile_header_2 {
display: flex;
}

/* NuxtLink は最終的に a タグになるため、a タグセレクタを使用 */
.sell_items, .buy_items {
color: #5f5f5f;
font-weight: 800;
text-decoration: none;
position: relative;
padding-bottom: 5px;
transition: color 0.2s;
}

.sell_items {
left: 70px;
}

.buy_items {
left: 120px;
}

.sell_items:hover,
.buy_items:hover {
color: #ff8888;
}

.sell_items.active,
.buy_items.active {
color: #ff5555;
}

/* アクティブなタブの下線 */
.sell_items.active::after,
.buy_items.active::after {
content: "";
position: absolute;
bottom: -1px;
left: 0;
width: 100%;
height: 3px;
background-color: #ff5555;
border-radius: 2px;
}

/* 成功・エラーメッセージのスタイル */
.validation-errors {
position: fixed;
top: 50px;
left: 50%;
transform: translateX(-50%);
max-width: 400px;
width: 90%;
padding: 10px;
background-color: #d4edda;
border: 1px solid #c3e6cb;
border-radius: 8px;
z-index: 100;
text-align: center;
color: #155724;
box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* ローディングスピナーとメッセージのためのユーティリティ */
.flex.justify-center.items-center.h-screen {
display: flex;
justify-content: center;
align-items: center;
height: 100vh;
}
.animate-spin {
animation: spin 1s linear infinite;
}
@keyframes spin {
from { transform: rotate(0deg); }
to { transform: rotate(360deg); }
}

/* レスポンシブ対応 (最小限) */
@media (max-width: 1024px) {
.items_select {
grid-template-columns: repeat(3, 1fr);
gap: 30px;
padding: 30px;
}
.user_image_css, .user_name_css, .user_edit_css2 {
position: static;
margin: 0 10px;
}
.profile_header_1 {
justify-content: center;
flex-wrap: wrap;
height: auto;
padding: 20px 0;
}
.user_edit_css1 {
margin: 10px auto;
width: 100%;
text-align: center;
}
.user_edit_css2 {
width: 80%;
max-width: 200px;
}
.sell_items, .buy_items {
left: 0;
margin: 0 20px;
}
.profile_header_2 {
justify-content: center;
}
}

@media (max-width: 640px) {
.items_select {
grid-template-columns: repeat(2, 1fr);
gap: 20px;
padding: 20px 10px;
}
.profile_page {
padding: 0 10px;
}
.sell_items, .buy_items {
margin: 0 10px;
}
}
</style>