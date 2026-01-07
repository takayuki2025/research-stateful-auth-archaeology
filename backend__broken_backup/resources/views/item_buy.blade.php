@extends('layouts.app_logout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/item_buy.css') }}">
@endsection

@section('content')

    <div class="item_buy_contents">

        <form action="{{ route('thanks_buy_create') }}" method="POST" class="test">
            @csrf
            <div class="item_buy_lr">
                <div class="item_buy_l">
                    <div class="item_buy_content1">
                        <div class="item_buy_image">
                            <img src="{{ asset($item->item_image) }}" alt="商品の画像">
                        </div>
                        <h3 class="item_name">{{ $item->name }}</h3>
                        <h2 class="item_price">¥{{ number_format($item->price) }}</h2>
                    </div>

                    <div class="item_buy_content2">
                        <h4 class="item_pay">支払い方法</h4>
                        <select name="payment" id="payment_select">
                            <option value="">支払いを選択してください</option>
                            <option value="コンビニ払い">コンビニ払い</option>
                            <option value="カード支払い">カード支払い</option>
                        </select>
                        @error('payment')
                            <div class="error_buy">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="item_buy_content3">
                        <div class="item_edit">
                            <h4 class="item_address">配送先</h4>
                            <a href="{{ route('item.purchase.edit', ['item_id' => $item->id, 'user_id' => Auth::id()]) }}" class="item_edit_a">変更する</a>
                        </div>
                        <h5 class="item_address_view1">{{ $user->post_number}}</h5>
                        <h5 class="item_address_view2">{{ $user->address }}</h5>
                        <h5 class="item_address_view2">{{ $user->building }}</h5>
                        @error('address')
                            <div class="error_buy">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="item_buy_r">
                    <div class="item_buy_select">
                        <div class="buy_price">
                            <p class="price_view"> 商品代金: 　　　￥{{ number_format($item->price) }}</p>
                        </div>
                        <div class="buy_payment">
                            <p class="pay_view">支払い方法: <span id="selected_payment_text">なし</span></p>
                        </div>
                    </div>

        <div class="item_buy_form">
            @if($item->remain > 0)
                <input type="hidden" name="item_id" value="{{ $item->id }}">
                <input type="hidden" name="address" value="{{ $user->address }}">
                <input type="submit" class="item_buy_submit" value="購入する">
            @else
        <div class="sold_out_message">
            <p>sold</p>
        </div>
            @endif
        </div>

                    @error('item_id')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentSelect = document.getElementById('payment_select');
            const selectedPaymentText = document.getElementById('selected_payment_text');

            paymentSelect.addEventListener('change', function() {
                const selectedOptionText = this.options[this.selectedIndex].text;
                selectedPaymentText.textContent = selectedOptionText;
            });
        });
    </script>

@endsection