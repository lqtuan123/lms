<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="tabs-listing">
                    <nav>
                        <div class="nav nav-tabs d-flex justify-content-center" id="nav-tab" role="tablist">
                            <button class="nav-link text-capitalize active" id="nav-sign-in-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-sign-in" type="button" role="tab" aria-controls="nav-sign-in"
                                aria-selected="true">Sign In</button>
                            <button class="nav-link text-capitalize" id="nav-register-tab" data-bs-toggle="tab"
                                data-bs-target="#nav-register" type="button" role="tab"
                                aria-controls="nav-register" aria-selected="false">Register</button>
                        </div>
                    </nav>
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade active show" id="nav-sign-in" role="tabpanel"
                            aria-labelledby="nav-sign-in-tab">
                            <form method="POST" action="{{ route('front.login.submit') }}">
                                @csrf
                                <div class="form-group py-3">
                                    <label class="mb-2" for="sign-in">Username or email address *</label>
                                    <input type="text" name="email" id="email" value="{{ old('email') }}"
                                        placeholder="Nhập Email"
                                        class="form-control w-100 rounded-3 p-3 @error('email') is-invalid @enderror"
                                        required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group pb-3">
                                    <label class="mb-2" for="sign-in">Password *</label>
                                    <input type="password" name="password" id="password" placeholder="Nhập mật khẩu"
                                        class="form-control w-100 rounded-3 p-3" required>
                                </div>
                                <label class="py-3">
                                    <input type="checkbox" required="" class="d-inline">
                                    <span class="label-body">Remember me</span>
                                </label>
                                <div class="form-group pb-3">
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="fw-bold">Quên mật
                                            khẩu?</a>
                                    @endif
                                </div>
                                <input type="hidden" name="plink" value="{{ url()->full() }}">
                                <button type="submit" name="submit" class="btn btn-dark w-100 my-3">Login</button>
                            </form>
                        </div>
                        @include('frontend.auth.register')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
