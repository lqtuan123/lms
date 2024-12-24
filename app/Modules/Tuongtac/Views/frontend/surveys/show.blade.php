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
    display:flex;
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
    @if(auth()->id() == $survey->user_id || (auth()->id()&&auth()->user()->role=='admin'))
        <div class="top-bar" style="margin:10px">
            <a href="{{ route('front.surveys.addquestion',$survey->id ) }}" class="btn btn-medium btn-base-color btn-round-edge left-icon btn-box-shadow">Thêm câu hỏi</a>
        </div>

    @endif
        
    @if($survey->hasUserAnswered(auth()->id()) )
        <div>
            <h3>Kết quả thăm dò: {{ $survey->name }}</h3>
            <?php $i = 0; ?>
            @foreach($survey->questions as $question)
            <?php $i++; ?>
                <div class="question">
                    <h3>{{$i}}.{{ $question->question }}</h3>
                    <ul class="list-group">
                        @foreach($question->options as $option)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $option->option_text }}</span>
                                <span>
                                    {{ $option->votes }} lượt chọn
                                    @if($question->options->sum('votes') > 0)
                                        ({{ round(($option->votes / $question->options->sum('votes')) * 100, 2) }}%)
                                    @else
                                        (0%)
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    
                </div>
            @endforeach
        </div>
    @endif
    @if(!$survey->hasUserAnswered(auth()->id()) || auth()->id() == $survey->user_id || (auth()->id()&&auth()->user()->role=='admin'))
        <div style="position:relative">
            <form id='responseForm' action="{{ route('front.poll.voteAll') }}" method="POST">
                @csrf
                <input type="hidden" name="survey_id" value="{{$survey->id}}" />
                <?php
                $i = 0;

                ?>
                @foreach($questions as $question)
                <?php
                    $i++;
                    ?>
                    <div style="position:relative" class="question post-card">
                        @if(auth()->id() == $survey->user_id || (auth()->id()&&auth()->user()->role=='admin'))
                        <div class="action-buttons" style="position: absolute; top: 5px;  right: 25px;z-index:1000">
                            <a href="{{ route('front.surveys.editquestion', $question->id)}}"  style=" ">
                                
                                    <i class="feather icon-feather-edit icon-extra-small  " style="background:white"></i> 
                                
                            </a>
                        
                            <!-- Nút Delete -->
                            <a onclick="return confirm('Bạn có chắc muốn câu hỏi này?');" href="{{ route('front.surveys.destroyquestion', $question->id)}}"  style=" ">
                                
                                    <i class="feather icon-feather-trash icon-extra-small  " style="background:white"></i> 
                                
                            </a>
                                {{-- <button type="submit" class="deletebtn" onclick="return confirm('Bạn có chắc muốn câu hỏi này?');"><i class="feather icon-feather-trash icon-extra-small  " style="background:white"></i></button> --}}
                            
                        </div>
                        @endif
                        <div class="post-item questionf"  data-question-st="{{$i}}"  data-question-id="{{$question->id}}">
                            <h3>{{$i}}.{{ $question->question }}</h3>
                            <div class="form-group" id="options-container-{{ $question->id }}">
                                @foreach($question->options as $option)
                                    <div class="formcheck">
                                        <input class="formcheck-input" type="radio" name="answers[{{ $question->id }}]" id="option-{{ $option->id }}" value="{{ $option->id }}" required>
                                        <label class="formcheck-label" for="option-{{ $option->id }}">
                                            {{ $option->option_text }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Thêm tùy chọn mới -->
                            <div id="new-options-{{ $question->id }}">
                                <!-- Các tùy chọn mới sẽ được thêm ở đây -->
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addOption({{ $question->id }})">Thêm tùy chọn mới</button>
                        </div>
                    </div>
                @endforeach

                {{-- <button type="submit" class="btn btn-medium btn-base-color btn-round-edge left-icon btn-box-shadow">Gửi câu trả lời</button> --}}
            </form>
            <button type="button" class="btn btn-medium btn-base-color btn-round-edge left-icon btn-box-shadow" id="submitBtn">Gửi câu trả lời</button>

    <!-- Popup Đăng Nhập -->
            
        </div>
    @endif
 
@endsection
@section('botscript')
<script>
   
   function addOption(questionId) {
        const optionsContainer = document.getElementById(`options-container-${questionId}`);
        const newOptionDiv = document.createElement('div');

        // Tạo một ID duy nhất cho tùy chọn mới
        const uniqueId = `new-option-${questionId}-${optionsContainer.childElementCount + 1}`;
        const optionInputName = `new_options[${questionId}][]`;

        newOptionDiv.classList.add('formcheck');
        newOptionDiv.innerHTML = `
            <input type="radio" class="formcheck-input" name="answers[${questionId}]" id="${uniqueId}" value="">
            <input type="text" class="form-control formcheck-label" placeholder="Nhập tùy chọn mới" oninput="updateOptionValue('${uniqueId}', this)" />
        `;

        // Thêm tùy chọn mới vào container
        optionsContainer.appendChild(newOptionDiv);
    }

    function updateOptionValue(optionId, input) {
        // Cập nhật giá trị của radio button dựa trên input text
        const radioButton = document.getElementById(optionId);
        radioButton.value = input.value;
    }
    
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const loginPopup = document.getElementById('loginPopup');
        const loginForm = document.getElementById('ajaxLoginForm');
        const loginError = document.getElementById('loginError');
    
        // Hiển thị popup
        document.getElementById('submitBtn').addEventListener('click', function () {

            // Lấy tất cả các câu hỏi
    const questions = document.querySelectorAll('.questionf');
    let allValid = true;

            // Kiểm tra từng câu hỏi
            questions.forEach(question => {
                const questionst = question.getAttribute('data-question-st');
                const questionId = question.getAttribute('data-question-id');
                const radioOptions = question.querySelectorAll(`input[name="answers[${questionId}]"]`);
                const newOptions = question.querySelectorAll(`input[name="new_options[${questionId}][]"]`);
                
                // Kiểm tra ít nhất một tùy chọn đã được chọn hoặc có giá trị
                const isRadioChecked = Array.from(radioOptions).some(option => option.checked);
                const isNewOptionFilled = Array.from(newOptions).some(option => option.value.trim() !== '');

                if (!isRadioChecked && !isNewOptionFilled) {
                    allValid = false;
                    alert(`Bạn chưa chọn hoặc thêm tùy chọn mới cho câu hỏi ${questionId}`);
                }
            });

            // Nếu tất cả các câu hỏi hợp lệ, submit form
            if (!allValid)  
            {
                return ;
            }
            else
            {
                const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
                if (!isAuthenticated) {
                    loginPopup.style.display = 'flex';
                } else {
                    // Nếu đã đăng nhập, submit form gửi câu trả lời
                    document.getElementById('responseForm').submit();
                }
            }
           
        });
    
        // Xử lý form đăng nhập
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Ngăn form gửi thông thường
    
            const formData = new FormData(loginForm);
            fetch('{{ route('ajax.login') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loginPopup.style.display = 'none';
                        alert(data.message);
                        // Gửi form câu trả lời sau khi đăng nhập thành công
                        document.getElementById('responseForm').submit();
                    } else {
                        loginError.style.display = 'block';
                        loginError.textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    loginError.style.display = 'block';
                    loginError.textContent = 'Đã xảy ra lỗi. Vui lòng thử lại.';
                });
        });
    
        // Đóng popup
      
    });
    </script>
@endsection
 