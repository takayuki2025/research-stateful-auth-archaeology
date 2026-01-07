@extends('layouts.app_logout')

@section('css')

<link rel="stylesheet" href="{{ asset('css/front_page.css') }}">
@endsection

@section('content')

<div class="main_contents">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="main_select">
        {{-- 検索クエリが存在すればURLに付加する --}}
        <a href="/{{ request()->query('all_item_search') ? '?all_item_search=' . request()->query('all_item_search') : '' }}"
        class="recs {{ ($tab === 'all') ? 'active' : '' }}">
            おすすめ
        </a>
        {{-- tab=mylistと現在の検索クエリを両方付加する --}}
        <a href="/?tab=mylist{{ request()->query('all_item_search') ? '&all_item_search=' . request()->query('all_item_search') : '' }}" 
        class="mylists {{ ($tab === 'mylist') ? 'active' : '' }}">
            マイリスト
        </a>
    </div>


    <div class="items_select">
        @foreach ($items as $item)
            <div class="items_select_all">
                <a href="/item/{{ $item->id }}">
                    {{-- 💡 最終修正: asset() を外し、スラッシュ (/) と rawurlencode() を併用します。 --}}
                    {{-- rawurlencode() が実行され、二重エンコードを防げます。 --}}
                    <img src="{{ '/' . rawurlencode($item->item_image) }}" alt="商品写真">
                </a>
                <div class="item-info">
                    <p class="item-name">{{ $item->name }}</p>
                    @if($item->remain === 0)
                        <p class="sold-text">sold</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>

@endsection