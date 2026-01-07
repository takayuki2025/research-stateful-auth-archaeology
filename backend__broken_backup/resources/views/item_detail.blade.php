@extends('layouts.app_logout')

@section('css')

<link rel="stylesheet" href="{{ asset('css/item_detail.css') }}">
@endsection

@section('content')

<div class="item_detail_contents">
    <div class="item_detail_image">
        <img class="item_detail_image1" src="{{ asset($item->item_image) }}" alt="商品写真">
    </div>

    <div class="information">
        <div class="item_detail_name">
            <h2>{{ $item->name }}</h2>
        </div>
        <div class="item_detail_brand">
            <p class="item_detail_brand_1">ブランド名</p>
            <p class="item_detail_brand_2">{{ $item->brand }}</p>
        </div>
        <div class="item_detail_price">

                @if ($item->remain < 1)
                    <h2>sold</h2>
                @else
                    <h2><span class="price_after">¥</span>{{ number_format($item->price) }}<span class="price_after"> (税込)</span></h2>
                @endif

        </div>
        <div class="item_detail_icon">
        @if(Auth::check() && Auth::id() != $item->user_id)
            <form action="{{ route('item.favorite', ['item' => $item->id]) }}" method="POST" class="favorite_form">
                @csrf
                <button type="submit" class="favorite_button">
                <div class="user_ster_icon">
        @if ($isFavorited)
            <span class="ster_icon_1">★</span>
        @else
            <span class="ster_icon_2">⭐︎</span>
        @endif
                </div>
                </button>
            </form>
        @endif
        @if(!Auth::check() || Auth::id() == $item->user_id)
            <div class="star_text_container">
                <span class="star_text">⭐︎</span>
            </div>
        @endif
            <p class="favorites_count">{{ $favoritesCount }}</p>
                    <span class="comments_icon">&#128172;</span>
                    <p class="comments_count0">{{ $comments->count() }}</p>
            </div>
            <div class="item_detail_form">
                <form action="{{ route('item_buy', ['item_id' => $item->id]) }}" method="get">
                    @csrf
                    @if(Auth::check() && Auth::id() != $item->user_id)
                        <input type="submit" value="購入手続きへ" class="info_submit" @if ($item->remain < 1) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                    @elseif(Auth::check() && Auth::id() == $item->user_id)
                        <a href="/mypage" class="info_submit">マイページへ移動する</a>
                    @else
                        <a href="{{ route('login') }}" class="info_submit">ログインして購入</a>
                    @endif
                </form>
            </div>
            <div class="item_detail_explain">
                <h2>商品説明</h2>
                    <h3 class="explain_word">{{ $item->explain }}</h3>
            </div>
            <div class="item_detail_category">
            <div>
                <h2>商品情報</h2>
                    @if ($item->category)
                    @php
                        $categories = is_string($item->category) ? json_decode($item->category, true) : $item->category;
                        // json_decodeが失敗した場合に備えて、$categoriesが配列であることを確認する
                    if (!is_array($categories)) {
                        $categories = [];
                    }
                    @endphp
            <ul class="category_views">
                <li class="category_mark01">カテゴリー</li>
                    @foreach ($categories as $category)
                        <li class="category_mark">{{ $category }}</li>
                    @endforeach
            </ul>
                    @else
                        <p>カテゴリーは登録されていません。</p>
                    @endif
            </div>
            </div>
            <div class="item_detail_condition">
                <h3 class="item_detail_condition_1">商品の状態　</h3>
                <h3 class="item_detail_condition_2">{{ $item->condition }}</h3>
            </div>
            <div class="item_detail_comment_history">
                <div class="comment_count_flex">
                    <h2>コメント</h2>
                    <span class="comments_count">({{ $comments->count() }})</span>
                </div>
                    @forelse($comments as $comment)
                <div class="comment">
                <div class="comment_name_image">
                    <img src="{{ isset($comment->user->user_image) && $comment->user->user_image ? asset($comment->user->user_image) : asset('/storage/images/default-profile2.jpg') }}" alt="プロフィール画像" class="user_image_css">
                    <p class="comment_name">{{ $comment->user->name }}</p>
                </div>
                    <p class="comment-text">{{ $comment->comment }}</p>
                        <small style="font-size:11px">投稿日時: {{ $comment->created_at->format('Y/m/d H:i') }}</small>
                </div>

            @empty
                <p>まだコメントはありません。</p>
            @endforelse

            </div>


            <div class="item_detail_comment_form">
                <h2></h2>
            <div class="item_detail_comment_form">
                @auth
                    <h2 class="comment_word">商品へのコメント</h2>
                @if (count($errors) > 0)
                    <ul class='error_massage'>
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                    </ul>
                @endif
            <form action="{{ route('comment_create') }}" method="post" class="comment_form">
                @csrf
                    <textarea name="comment" rows="5" cols="47" placeholder="コメントを入力してください"></textarea>
                    <input type="submit" value="コメントを送信する" class="comment_submit">
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
            </form>
                @else
            <h2></h2>
            <a href="{{ route('login') }}" class="comment_submit">ログインしてコメントする</a>
        @endauth
    </div>
</div>

@endsection