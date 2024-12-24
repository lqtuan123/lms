@extends('Tuongtac::frontend_laydies.surveys.body')
@section('topcss')
@endsection
<?php

use Carbon\Carbon;

$adsense_code = '<ins class="adsbygoogle"
            style="display:block; text-align:center;"
            data-ad-layout="in-article"
            data-ad-format="fluid"
            data-ad-client="ca-pub-5437344106154965"
            data-ad-slot="3375673265"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>';
 

?>
@section('inner-content')
              
<div class="container">
    <h3>Thêm nhóm thăm dò mới</h3>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('front.surveys.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Tên Survey</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <input type="hidden"  id="item_code" name="item_code" value="{{$item_code  }}">
        <input type="hidden"  id="item_id" name="item_id" value="{{$item_id  }}">
        <div class="form-group">
            <label for="expired_date">Ngày hết hạn (tùy chọn)</label>
            <input type="date" class="form-control @error('expired_date') is-invalid @enderror" id="expired_date" name="expired_date" 
            value="{{ old('expired_date', \Carbon\Carbon::now()->addDays(7)->toDateString()) }}">
            @error('expired_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" style="margin:5px" class="btn btn-medium btn-base-color btn-round-edge left-icon btn-box-shadow">Thêm Survey</button>
    </form>
</div>
 
@endsection
 