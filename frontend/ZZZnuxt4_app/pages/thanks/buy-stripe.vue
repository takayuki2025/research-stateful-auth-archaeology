<template>
  <div class="thank-you-page">
    <div class="message-box">
      <h1>ご購入ありがとうございます！</h1>
      <p class="mt-4 text-lg">
        Strip決済処理完了致しました。<br />
        商品発送完了までしばらくお待ちください。
      </p>
      <p class="mt-6">
        <router-link to="/" class="back-home-link">
          ホームへ戻る
        </router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.thank-you-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  text-align: center;
  background-color: #f7f7f7;
}
.message-box {
  background: white;
  padding: 40px;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
h1 {
  font-size: 2rem;
  color: #ef4444; /* 赤色 */
}
.back-home-link {
  color: #3b82f6; /* 青色 */
  text-decoration: underline;
  font-weight: bold;
}
</style>