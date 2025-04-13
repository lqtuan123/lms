@extends('frontend.layouts.master')

@section('content')

<!-- Banner Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row py-5 align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <h1 class="display-4 fw-bold mb-4">Liên hệ với chúng tôi</h1>
                <p class="lead">Hãy để lại thông tin, chúng tôi sẽ phản hồi sớm nhất có thể.</p>
            </div>
            <div class="col-md-6 text-center">
                <img src="{{ asset('frontend/assets_f/images/contact-banner.svg') }}" alt="Contact" class="img-fluid" style="max-height: 250px;">
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Information -->
            <div class="col-lg-4">
                <h3 class="mb-4">Thông tin liên hệ</h3>
                
                <div class="d-flex mb-4">
                    <div class="me-3">
                        <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h5>Địa chỉ</h5>
                        <p class="mb-0">{{ $detail->address ?? 'Ywang Buôn Ma Thuột, Đăk Lăk' }}</p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="me-3">
                        <i class="fas fa-phone fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h5>Điện thoại</h5>
                        <p class="mb-0">{{ $detail->phone ?? '0384339011' }}</p>
                    </div>
                </div>
                
                <div class="d-flex mb-4">
                    <div class="me-3">
                        <i class="fas fa-envelope fa-2x text-primary"></i>
                    </div>
                    <div>
                        <h5>Email</h5>
                        <p class="mb-0">{{ $detail->email ?? 'contact@bookly-lms.com' }}</p>
                    </div>
                </div>
                
                <div class="mt-5">
                    <h5>Kết nối với chúng tôi</h5>
                    <div class="d-flex mt-3">
                        <a href="#" class="me-3 social-icon">
                            <svg class="facebook" width="24" height="24">
                                <use xlink:href="#facebook"></use>
                            </svg>
                        </a>
                        <a href="#" class="me-3 social-icon">
                            <svg class="twitter" width="24" height="24">
                                <use xlink:href="#twitter"></use>
                            </svg>
                        </a>
                        <a href="#" class="me-3 social-icon">
                            <svg class="instagram" width="24" height="24">
                                <use xlink:href="#instagram"></use>
                            </svg>
                        </a>
                        <a href="#" class="social-icon">
                            <svg class="youtube" width="24" height="24">
                                <use xlink:href="#youtube"></use>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="mb-4">Gửi tin nhắn cho chúng tôi</h3>
                        
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        <form action="{{ route('contact.submit') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Họ và tên</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    @error('email')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" id="phone" name="phone">
                                    @error('phone')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="subject" class="form-label">Chủ đề</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                    @error('subject')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Nội dung</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                    @error('message')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary px-5 py-3">Gửi tin nhắn</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Địa chỉ của chúng tôi</h2>
            <p class="lead">Ghé thăm văn phòng của chúng tôi</p>
        </div>
        
        <div class="ratio ratio-21x9" style="height: 400px;">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3898.7092914964663!2d108.05125561383868!3d12.243445533578588!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3172b1ad0119108b%3A0xf7e36e920b2a1efa!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBUw6J5IE5ndXnDqm4!5e0!3m2!1svi!2s!4v1649644485698!5m2!1svi!2s" 
                style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2>Câu hỏi thường gặp</h2>
            <p class="lead">Một số câu hỏi thường gặp về hệ thống của chúng tôi</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Làm thế nào để tạo tài khoản trên Bookly-LMS?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Để tạo tài khoản, bạn chỉ cần nhấp vào biểu tượng người dùng ở góc trên bên phải trang web và chọn "Đăng ký". Sau đó, điền đầy đủ thông tin cá nhân và làm theo hướng dẫn để hoàn tất quá trình đăng ký.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Làm thế nào để tải lên sách hoặc tài liệu?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sau khi đăng nhập, bạn có thể nhấp vào "Đăng sách" trong menu người dùng. Điền đầy đủ thông tin về sách, tải lên tệp và hình ảnh bìa, sau đó gửi để được duyệt. Quản trị viên sẽ xem xét và phê duyệt sách của bạn.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Tôi có thể tạo nhóm học tập trên Bookly-LMS không?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Có, bạn có thể tạo nhóm học tập trên Bookly-LMS. Sau khi đăng nhập, truy cập vào phần "Nhóm" và chọn "Tạo nhóm mới". Thiết lập thông tin nhóm, mời thành viên, và bắt đầu chia sẻ tài liệu trong nhóm của bạn.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Có hạn chế về dung lượng tải lên tài liệu không?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Mỗi tệp tải lên có giới hạn dung lượng tối đa là 20MB. Nếu tài liệu của bạn lớn hơn, bạn có thể chia nhỏ thành nhiều phần hoặc liên hệ với quản trị viên để được hỗ trợ.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection 