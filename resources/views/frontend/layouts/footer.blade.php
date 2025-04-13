<?php $detail = \App\Models\SettingDetail::find(1); ?>

<footer id="footer" class="padding-large" style="padding-bottom: 20px;">
  <div class="container">
    <div class="row">
      <div class="footer-top-area">
        <div class="row d-flex flex-wrap justify-content-between">
          <div class="col-lg-3 col-sm-6 pb-3">
            <div class="footer-menu">
              <img src="{{ asset('frontend/assets_f/images/main-logo.png')}}" alt="logo" class="img-fluid mb-2">
              <p>Bookly là nền tảng chia sẻ tài liệu và hỗ trợ học tập dành cho sinh viên công nghệ thông tin và những người yêu sách.</p>
              <div class="social-links">
                <ul class="d-flex list-unstyled">
                  <li><a href="#"><svg class="facebook"><use xlink:href="#facebook" /></svg></a></li>
                  <li><a href="#"><svg class="instagram"><use xlink:href="#instagram" /></svg></a></li>
                  <li><a href="#"><svg class="twitter"><use xlink:href="#twitter" /></svg></a></li>
                  <li><a href="#"><svg class="linkedin"><use xlink:href="#linkedin" /></svg></a></li>
                  <li><a href="#"><svg class="youtube"><use xlink:href="#youtube" /></svg></a></li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-lg-2 col-sm-6 pb-3">
            <div class="footer-menu text-capitalize">
              <h5 class="widget-title pb-2">Liên kết nhanh</h5>
              <ul class="menu-list list-unstyled text-capitalize">
                <li class="menu-item mb-1"><a href="#">Trang chủ</a></li>
                <li class="menu-item mb-1"><a href="#">Giới thiệu</a></li>
                <li class="menu-item mb-1"><a href="#">Thư viện sách</a></li>
                <li class="menu-item mb-1"><a href="#">Blog</a></li>
                <li class="menu-item mb-1"><a href="#">Liên hệ</a></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-3 col-sm-6 pb-3">
            <div class="footer-menu text-capitalize">
              <h5 class="widget-title pb-2">Hỗ trợ</h5>
              <ul class="menu-list list-unstyled">
                <li class="menu-item mb-1"><a href="#">Hướng dẫn sử dụng</a></li>
                <li class="menu-item mb-1"><a href="#">Chính sách hoàn trả</a></li>
                <li class="menu-item mb-1"><a href="#">Giao hàng & thanh toán</a></li>
                <li class="menu-item mb-1"><a href="#">Câu hỏi thường gặp</a></li>
              </ul>
            </div>
          </div>

          <div class="col-lg-3 col-sm-6 pb-3">
            <div class="footer-menu contact-item">
              <h5 class="widget-title text-capitalize pb-2">Liên hệ</h5>
              <p>Mọi thắc mắc hoặc góp ý xin gửi về: <a href="mailto:lequoctuan.dev@gmail.com" class="text-decoration-underline">lequoctuan.dev@gmail.com</a></p>
              <p>Hoặc gọi trực tiếp: <a href="#" class="text-decoration-underline">+84 987 654 321</a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>

<hr>

<div id="footer-bottom" class="mb-2">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between">
      {{-- <div class="ship-and-payment d-flex gap-md-5 flex-wrap">
        <div class="shipping d-flex">
          <p>Vận chuyển bởi:</p>
          <div class="card-wrap ps-2">
            <img src="{{ asset('frontend/assets_f/images/dhl.png')}}" alt="dhl">
            <img src="{{ asset('frontend/assets_f/images/shippingcard.png')}}" alt="ship">
          </div>
        </div>
        <div class="payment-method d-flex">
          <p>Phương thức thanh toán:</p>
          <div class="card-wrap ps-2">
            <img src="{{ asset('frontend/assets_f/images/visa.jpg')}}" alt="visa">
            <img src="{{ asset('frontend/assets_f/images/mastercard.jpg')}}" alt="mastercard">
            <img src="{{ asset('frontend/assets_f/images/paypal.jpg')}}" alt="paypal">
          </div>
        </div>
      </div> --}}
      <div class="copyright">
        <p>© 2024 Bookly. Thiết kế & phát triển bởi <a href="#" target="_blank">Lê Quốc Tuấn</a></p>
      </div>
    </div>
  </div>
</div>
