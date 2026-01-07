@extends('layouts.app_logout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')

<div class="profile_page">
    <div class="profile_header">
        <div class="profile_header_1">
            <img src="{{ isset($user->user_image) && $user->user_image ? asset($user->user_image) : asset('/storage/images/default-profile2.jpg') }}" alt="プロフィール画像" class="user_image_css">
            <h2 class="user_name_css">{{ $user['name'] }}</h2>
            <form action="{{ route('profile_edit') }}" method="get" class="user_edit_css1">
                @csrf
                <input type="submit" class="user_edit_css2" value="プロフィールを編集">
            </form>
        </div>
        <div class="profile_header_2">
            <a href="/mypage?page=sell" class="sell_items @if($page === 'sell') active @endif">出品した商品</a>
            <a href="/mypage?page=buy" class="buy_items @if($page === 'buy') active @endif">購入した商品</a>
        </div>
    </div>

    <div class="profile_content">
        @if ($items->isEmpty())
            <p>{{ $page === 'sell' ? '出品した商品はありません。' : '購入した商品はありません。' }}</p>
        @else
            <div class="items_select">
                @foreach ($items as $item)
                    <div class="items_select_all">
                        @if ($page === 'sell')
                            <a href="/item/{{ $item->id }}" class="mypage_item_">
                                <img src="{{ asset($item->item_image) }}" alt="商品写真">
                                <div class="item-details">
                                    <label>{{ $item->name }}</label>
                                    @if ($item->remain === 0)
                                        <span class="sold-text">sold</span>
                                    @endif
                                </div>
                            </a>
                        @elseif ($page === 'buy')
                            <!-- 購入履歴の場合は、関連する商品データにアクセス -->
                            <a href="/item/{{ $item->item->id }}" class="mypage_item_">
                                <img src="{{ asset($item->item->item_image) }}" alt="商品写真">
                                <div class="item-details">
                                    <label>{{ $item->item->name }}</label>
                                    @if ($item->item->remain === 0)
                                        <span class="sold-text">sold</span>
                                    @endif
                                </div>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection