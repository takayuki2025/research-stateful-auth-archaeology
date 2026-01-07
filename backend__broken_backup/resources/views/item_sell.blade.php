@extends('layouts.app_logout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/item_sell.css') }}">
@endsection

@section('content')

    <div class="item_sell_contents">
        <div class="item_sell_contents_box">

            <div class="small_box">

                <h1 class="item_sell_contents_box_title">商品の出品</h1>

                <label class="item_sell_contents_box_imagetitle">商品画像</label>

                <form action="/upload" enctype="multipart/form-data" method="post" class="item_sell_contents_box_line">
                    @csrf

                    <button type="button" class="upload_submit" onclick="document.getElementById('fileInput').click()">画像を選択する</button>

                    <input type="file" name="item_image" id="fileInput" style="display: none;">

                    @if (session('success'))
                        <div class="alert-success">
                            {{ session('success') }}
                        </div>
                    @endif



                    @error('item_image')
                        <div class="alert_error">{{ $message }}</div>
                    @enderror

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

                <div class="sell_title1">
                    <h2>商品の詳細</h2>
                </div>

                <form action="/thanks_sell" method="post">
                    @csrf
                    <div class="sell_title1_1">
                        <label>カテゴリー</label>
                        <br>
                        <br>
    <div class="category-buttons-container">

    <!-- ユニークなIDとラベル -->
    <input type="checkbox" id="cat1" name="category[]" value="ファッション" class="category-checkbox-input" @if(is_array(old('category')) && in_array('ファッション', old('category'))) checked @endif>
    <label for="cat1" class="category-checkbox-label">ファッション</label>

    <input type="checkbox" id="cat2" name="category[]" value="家電" class="category-checkbox-input" @if(is_array(old('category')) && in_array('家電', old('category'))) checked @endif>
    <label for="cat2" class="category-checkbox-label">家電</label>

    <input type="checkbox" id="cat3" name="category[]" value="インテリア" class="category-checkbox-input" @if(is_array(old('category')) && in_array('インテリア', old('category'))) checked @endif>
    <label for="cat3" class="category-checkbox-label">インテリア</label>

    <input type="checkbox" id="cat4" name="category[]" value="レディース" class="category-checkbox-input" @if(is_array(old('category')) && in_array('レディース', old('category'))) checked @endif>
    <label for="cat4" class="category-checkbox-label">レディース</label>

    <input type="checkbox" id="cat5" name="category[]" value="メンズ" class="category-checkbox-input" @if(is_array(old('category')) && in_array('メンズ', old('category'))) checked @endif>
    <label for="cat5" class="category-checkbox-label">メンズ</label>

    <input type="checkbox" id="cat6" name="category[]" value="コスメ" class="category-checkbox-input" @if(is_array(old('category')) && in_array('コスメ', old('category'))) checked @endif>
    <label for="cat6" class="category-checkbox-label">コスメ</label>

    <input type="checkbox" id="cat7" name="category[]" value="本" class="category-checkbox-input" @if(is_array(old('category')) && in_array('本', old('category'))) checked @endif>
    <label for="cat7" class="category-checkbox-label">本</label>

    <input type="checkbox" id="cat8" name="category[]" value="ゲーム" class="category-checkbox-input" @if(is_array(old('category')) && in_array('ゲーム', old('category'))) checked @endif>
    <label for="cat8" class="category-checkbox-label">ゲーム</label>

    <input type="checkbox" id="cat9" name="category[]" value="スポーツ" class="category-checkbox-input" @if(is_array(old('category')) && in_array('スポーツ', old('category'))) checked @endif>
    <label for="cat9" class="category-checkbox-label">スポーツ</label>

    <input type="checkbox" id="cat10" name="category[]" value="キッチン" class="category-checkbox-input" @if(is_array(old('category')) && in_array('キッチン', old('category'))) checked @endif>
    <label for="cat10" class="category-checkbox-label">キッチン</label>

    <input type="checkbox" id="cat11" name="category[]" value="ハンドメイド" class="category-checkbox-input" @if(is_array(old('category')) && in_array('ハンドメイド', old('category'))) checked @endif>
    <label for="cat11" class="category-checkbox-label">ハンドメイド</label>

    <input type="checkbox" id="cat12" name="category[]" value="アクセサリー" class="category-checkbox-input" @if(is_array(old('category')) && in_array('アクセサリー', old('category'))) checked @endif>
    <label for="cat12" class="category-checkbox-label">アクセサリー</label>

    <input type="checkbox" id="cat13" name="category[]" value="おもちゃ" class="category-checkbox-input" @if(is_array(old('category')) && in_array('おもちゃ', old('category'))) checked @endif>
    <label for="cat13" class="category-checkbox-label">おもちゃ</label>

    <input type="checkbox" id="cat14" name="category[]" value="キッズ:ベビー" class="category-checkbox-input" @if(is_array(old('category')) && in_array('キッズ:ベビー', old('category'))) checked @endif>
    <label for="cat14" class="category-checkbox-label">キッズ:ベビー</label>
    </div>
        @error('category')
            <div class="error">{{ $message }}</div>
        @enderror
            <br>
    </div>

                    <div class="sell_title1_2">
                        <label>商品の状態</label>
                        <select name="condition" class="select_box">
                            <option value="" @if(empty(old('condition'))) selected @endif>選択してください</option>
                            <option value="良好" @if(old('condition') === '良好') selected @endif>良好</option>
                            <option value="目立った傷や汚れなし" @if(old('condition') === '目立った傷や汚れなし') selected @endif>目立った傷や汚れなし</option>
                            <option value="やや傷や汚れあり" @if(old('condition') === 'やや傷や汚れあり') selected @endif>やや傷や汚れあり</option>
                            <option value="状態が悪い" @if(old('condition') === '状態が悪い') selected @endif>状態が悪い</option>
                        </select>
                                @error('condition')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                    </div>

                    <div class="sell_title2">
                        <h2>商品名と説明</h2>
                    </div>

                    <div class="sell_title2_1">
                        <label>商品名</label>
                        <input type="text" name="name" class="sell_item_form" value="{{ old('name') }}">
                                @error('name')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                    </div>

                    <div class="sell_title2_2">
                        <label>ブランド名</label>
                        <input type="text" name="brand" class="sell_item_form" value="{{ old('brand') }}">
                                @error('brand')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                    </div>

                    <div class="sell_title2_3">
                        <label>商品の説明</label>
                        <textarea name="explain" class="sell_item_form_textarea">{{ old('explain') }}</textarea>
                                @error('explain')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                                </div>

                    <div class="sell_title2_4">
                        <label>販売価格</label>
                        <input type="text" name="price" class="sell_item_form2" value="{{ old('price') }}">
                            <span class="currency-symbol">¥</span>
                                @error('price')
                                    <div class="error">{{ $message }}</div>
                                @enderror
                    </div>

                    <div class="sell_title3">
                        <input type="submit" value="出品する" class="sell_item_submit">
                        <input type="hidden" name="item_image" value="{{ session('image_path') }}">
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection