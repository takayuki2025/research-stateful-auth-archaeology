@extends('layouts.app_logout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile_edit.css') }}">
@endsection

@section('content')

    <div class="login_page">
      <h2 class="title">プロフィール設定</h2>
      <label class="item_sell_contents_box_imagetitle"></label>

      <form action="/upload2" enctype="multipart/form-data" method="post" class="item_sell_contents_box_line">
        @csrf
          <div class="image_name">
            <img src="{{ isset($user->user_image) && $user->user_image ? asset($user->user_image) : asset('/storage/images/default-profile2.jpg') }}" alt="プロフィール画像" class="user_image_css">
              <button type="button" class="upload_submit" onclick="document.getElementById('fileInput').click()">画像を選択する</button>
                <input type="file" name="user_image" id="fileInput" style="display: none;">
          </div>
              @if (session('success'))
                <div class="alert-success2">
              {{ session('success') }}
            </div>
              @endif
                <div class="user_image_error_message">
              @error('user_image')
                {{ $message }}
              @enderror
            </div>
      </form>

      <script>
          // JavaScriptでファイルが選択されたら自動でフォームを送信
            const fileInput = document.getElementById('fileInput');
            fileInput.addEventListener('change', function() {
            // ファイルが選択されているか確認
            if (this.files.length > 0) {
            // 親のフォームを送信
            this.form.submit();
            }
            });
      </script>

      <form action='/profile_update' method="POST">
        @method('PATCH')
        @csrf
          <label class="label_form_1">ユーザー名</label>
              <input type="text" class="name_form" name="name" value="{{ old('name' , $user->name ?? '') }}"/>
                <div class="profile__error">
                  @error('name')
                  {{ $message }}
                  @enderror
                </div>
              <label class="label_form_2">郵便番号</label>
                <input type="text" class="email_form" name="post_number" value="{{ old('post_number' , $user->post_number ?? '') }}"/>
                  <div class="profile__error">
                    @error('post_number')
                    {{ $message }}
                    @enderror
                  </div>
              <label class="label_form_3">住所</label>
                <input type="text" class="password_form" name="address" value="{{ old('address' , $user->address ?? '') }}"/>
                  <div class="profile__error">
                    @error('address')
                    {{ $message }}
                    @enderror
                </div>
              <label class="label_form_4">建物名</label>
                <input type="text" class="password_form" name="building" value="{{ old('building' , $user->building ?? '') }}"/>
                  <div class="profile__error">
                    @error('building')
                    {{ $message }}
                    @enderror
                  </div>
              <div class="submit">
                <input type="submit" class="submit_form" value="更新する">
              </div>
                <input type="hidden" name="user_image" value="{{ session('image_path2') ?? ($user->user_image ?? '') }}">
      </form>
    </div>


@endsection