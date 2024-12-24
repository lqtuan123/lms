@extends('Tuongtac::frontend.surveys.body')
@section('topcss')

<style>
.question-block {
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    background-color: #f9f9f9;
}
 

/* Câu hỏi */
.question {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.question h3 {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 15px;
    color: #333;
}

/* Câu trả lời */
.formcheck {
    margin-bottom: 10px;
}

.formcheck-input {
    margin-right: 10px;
    transform: scale(1.3); /* Tăng kích thước radio button */
    width:auto;
}

.formcheck-label {
    font-size: 1.1rem;
    color: #555;
}

 
.list-group-item {
    font-size: 1.1rem;
}
/* Đường kẻ giữa các câu hỏi */
hr {
    border: none;
    border-top: 1px solid #ccc;
    margin: 20px 0;
}

.deletebtn {
    border: none;
    background: white;
}

    </style>
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
    <h1>Chỉnh sửa câu hỏi</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('front.surveys.updatequestion', $question->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="question_text">Câu hỏi</label>
            <input type="text" id="question_text" name="question_text" class="form-control" value="{{ $question->question }}" required>
        </div>

        <div id="answers">
            <h3>Câu trả lời</h3>
            @foreach($question->options as $option)
                <div class="form-group answer-input">
                    <input type="hidden" name="answers[{{ $loop->index }}][id]" value="{{ $option->id }}">
                    <input type="text" name="answers[{{ $loop->index }}][text]" class="form-control" value="{{ $option->option_text }}" required>
                </div>
            @endforeach
        </div>

        <button type="button" id="add-answer" class="btn btn-secondary">Thêm câu trả lời</button>
        <div>
            <button type="submit" style="margin:10px" class="btn btn-medium btn-base-color btn-round-edge left-icon btn-box-shadow">Lưu thay đổi</button>
        </div>
    </form>
</div>


@endsection

@section('botscript')
<script>
    let answerCount = {{ $question->options->count() }};
    document.getElementById('add-answer').addEventListener('click', function () {
        const answersDiv = document.getElementById('answers');
        const newAnswerDiv = document.createElement('div');
        newAnswerDiv.classList.add('form-group', 'answer-input');
        newAnswerDiv.innerHTML = `
            <input type="hidden" name="answers[${answerCount}][id]" value="">
            <input type="text" name="answers[${answerCount}][text]" class="form-control" placeholder="Nhập câu trả lời mới" required>
        `;
        answersDiv.appendChild(newAnswerDiv);
        answerCount++;
    });
</script>
@endsection


