@extends('layouts.app_logout')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="login_page">
    <div class="register_box">
    <h2 class="title">住所の変更</h2>

    <form action="{{ route('item.purchase.update', ['item_id' => $item_id, 'user_id' => $user_id]) }}" method="POST">
        @method('PATCH')
        @csrf


        <label class="label_form_5">郵便番号</label>
        <input type="text" class="email_form" name="post_number" value="{{ old('post_number' , $user['post_number']) }}"/>
            <div class="profile__error">
            @error('post_number')
            {{ $message }}
            @enderror
            </div>
        <label class="label_form_6">住所</label>
        <input type="text" class="password_form" name="address" value="{{ old('address' , $user['address']) }}"/>
            <div class="profile__error">
            @error('address')
            {{ $message }}
            @enderror
            </div>
        <label class="label_form_7">建物名</label>
        <input type="text" class="password_form" name="building" value="{{ old('building' , $user['building']) }}"/>
            <div class="profile__error">
            @error('building')
            {{ $message }}
            @enderror
            </div>
        <div class="submit">
            <input type="submit" class="submit_form" value="更新する">
        </div>
    </form>
</div>
</div>


@endsection