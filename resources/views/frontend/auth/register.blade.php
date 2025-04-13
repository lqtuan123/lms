<div class="tab-pane fade" id="nav-register" role="tabpanel" aria-labelledby="nav-register-tab">
    <form class="theme-form" method= "POST" action="{{ route('front.register') }}">
        @csrf

        {!! NoCaptcha::renderJs() !!}

        @if ($errors->has('g-recaptcha-response'))
            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
        @endif
        <div class="form-row row">
            <div class="col-md-6">
                <label for="email">Tên đầy đủ</label>
                <input type="text" name="full_name" class="form-control" id="full_name"
                    placeholder="tên đầy đủ" value="{{ old('full_name') }}" required>
            </div>
            <div class="col-md-6">
                <label for="review">Điện thoại</label>
                <input type="text" name="phone" class="form-control" id="phone"
                    placeholder="Số điện thoại" value="{{ old('phone') }}" required>
            </div>
        </div>
        <div class="form-row row">
            <div class="col-md-6">
                <label for="email">email</label>
                <input type="text" name="email" value="{{ old('email') }}"
                    class="form-control" id="register_email" placeholder="Email" required>
            </div>
            <div class="col-md-6">
                <label for="review">Địa chỉ</label>
                <input type="text" name="address" class="form-control" id="address"
                    value="{{ old('address') }}" placeholder="địa chỉ" required>

            </div>
            <div class="form-row row">
                <div class="col-md-6">
                    <label for="email">Mật khẩu</label>
                    <input type="password" name="password" class="form-control"
                        id="register_password" placeholder="password" required>
                </div>
                <div class="col-md-6">
                    <label for="review">Giới thiệu bản thân</label>
                    <input type="text" name="description" class="form-control"
                        id="register_description" placeholder="mô tả ngắn">
                </div>
                <div class="col-md-12">
                    <label for="review">1 + 1 =?</label>
                    <input type="text" name="ketqua" class="form-control"
                         placeholder="kết quả ghi bằng chữ viết thường">
                </div>
                {!! NoCaptcha::display() !!}
                <button type="submit" class="btn btn-solid w-auto">Đăng ký</button>
            </div>
    </form>
</div>