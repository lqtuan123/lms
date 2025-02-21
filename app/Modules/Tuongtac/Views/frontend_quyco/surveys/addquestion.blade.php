@extends('frontend_quyco.layouts.master')
@section('head_css')
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
@section('content')
@include('frontend_quyco.layouts.page_title')

<div class="container">
    @include('frontend_quyco.layouts.notification')
    <h1>Thêm Câu Hỏi và Câu Trả Lời</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('front.surveys.questionstore') }}" method="POST">
        @csrf
        <input type="hidden" name="survey_id" value="{{$survey_id}}" />
        <div class="form-group">
            <label for="question_text">Câu hỏi</label>
            <input type="text" class="form-control @error('question_text') 
                is-invalid @enderror" id="question_text" name="question_text" 
                value="{{ old('question_text') }}" required>
            @error('question_text')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <br/>
        <div id="answers">
            <h3>Câu trả lời</h3>
            @for ($i = 0; $i < 10; $i++)
                <div class="form-group answer-input">
                    <label for="answers[{{ $i }}]">Câu trả lời {{ $i + 1 }}</label>
                    <input type="text" class="form-control" name="answers[]" placeholder="Nhập câu trả lời">
                </div>
            @endfor
        </div>

        <button type="button" id="add-answer" class="btn btn-secondary">Thêm câu trả lời</button>
        <div style="margin:10px">
            <button type="submit" class="btn btn-medium btn-base-color btn-round-edge left-icon btn-box-shadow">Lưu</button>
        </div>
    </form>
</div>
 
@endsection
@section('footscripts')
<script>
    let answerCount = 10; // Bắt đầu với 10 ô nhập
    document.getElementById('add-answer').addEventListener('click', function () {
        const answersDiv = document.getElementById('answers');
        const newAnswerDiv = document.createElement('div');
        newAnswerDiv.classList.add('form-group', 'answer-input');
        newAnswerDiv.innerHTML = `
            <label for="answers[${answerCount}]">Câu trả lời ${answerCount + 1}</label>
            <input type="text" class="form-control" name="answers[]" placeholder="Nhập câu trả lời">
        `;
        answersDiv.appendChild(newAnswerDiv);
        answerCount++;
    });
</script>
@endsection