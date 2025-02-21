{{-- <footer class="!bg-[#21262c] shrink-0">
    <div class=" px-5 py-10 xl:!py-10 lg:!py-10 md:!py-10  ">
      <div class="flex flex-wrap mx-[-15px] mt-[-30px] xl:mt-0 lg:mt-0">
        <div class="md:w-4/12 xl:w-3/12 lg:w-3/12 w-full flex-[0_0_auto] px-[15px] max-w-full xl:mt-0 lg:mt-0 mt-[30px]">
          <div class="widget text_light_color">
            <img class="!mb-4" src="{{$setting->logo}}"   >
            <p class="!mb-4">
              {{$setting->memory}} 
            </p>
           
            <!-- /.social -->
          </div>
          <!-- /.widget -->
        </div>
        <!-- /column -->
        <div class="md:w-4/12 xl:w-3/12 lg:w-3/12 w-full flex-[0_0_auto] px-[15px] max-w-full xl:mt-0 lg:mt-0 mt-[30px]">
          <div class="widget text_light_color">
              <h4 class="widget-title text-white !mb-3"> {{$setting->company_name}}</h4>
              <ul class="contact-list" style="padding-left: 0rem;">
                <li><i  class="text-white uil uil-house-user"></i> {{$setting->address}}
                </li>
                <li><i class="text-white uil uil-phone-alt"></i> Điện thoại: {{$setting->phone}}</li>
                <li><i class="text-white uil uil-envelopes"></i> Email: {{$setting->email}} </li>
                <li><i class="text-white uil uil-book-alt"></i> Mst:  {{$setting->mst}} </li>
                <li><i class="text-white uil uil-headphones"></i> Hotline:  {{$setting->hotline}} </li>
              </ul>
          </div>
          <!-- /.widget -->
        </div>
        <!-- /column -->
        <div class="md:w-4/12 xl:w-3/12 lg:w-3/12 w-full flex-[0_0_auto] px-[15px] max-w-full xl:mt-0 lg:mt-0 mt-[30px]">
          <div class="widget text_light_color">
            <h4 class="widget-title text-white !mb-3">LIÊN KẾT HỮU ÍCH</h4>
            <ul class="pl-0 list-none   !mb-0">
                <li><a class="text_light_color" href="">Chính sách bảo mật</a></li>
                <li><a class="text_light_color" href="">Điều khoản và quy định</a></li>
                <li><a class="text_light_color" href="">Chính sách </a></li>
                <li><a class="text_light_color" href="">Chính sách </a></li>
                <li><a class="text_light_color" href="">Chính sách </a></li>
                <li><a class="text_light_color"href="">Tài khoản</a></li>

            </ul>
          </div>
          <!-- /.widget -->
        </div>
        <!-- /column -->
        <div class="md:w-full xl:w-3/12 lg:w-3/12 w-full flex-[0_0_auto] px-[15px] max-w-full xl:mt-0 lg:mt-0 mt-[30px]">
          <div class="widget text_light_color">
            <h4 class="widget-title text-white !mb-3">Thông tin khác</h4>
              <ul class="pl-0 list-none   !mb-0">
                <li><a class="text_light_color" href="">Cập nhật hồ sơ</a></li>
                
                <li><a class="text_light_color" href="">Liên hệ</a></li>
              </ul>
              <div class="py-5 newsletter-wrapper">
                  <nav class="nav social social-white">
                    <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]" href="{{$setting->facebook}}">
                      <img src="{{asset('frontend/assets_tp/images/icon/facenho.png')}}" class=" "/>
                    </a>
                    <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]" href="{{$setting->shopee}}">
                      <img src="{{asset('frontend/assets_tp/images/icon/shopeenho.png')}}" class=" "/>
                    </a>
                    <a class="text-[#cacaca] text-[1rem] transition-all duration-[0.2s] ease-in-out translate-y-0 motion-reduce:transition-none hover:translate-y-[-0.15rem] m-[0_.7rem_0_0]" href="{{$setting->lazada}}">
                      <img src="{{asset('frontend/assets_tp/images/icon/laznho.png')}}" class=" "/>
                    </a>
                  </nav> 
              </div>
              <!--End mc_embed_signup-->
            </div>
            <!-- /.newsletter-wrapper -->
          </div>
          <!-- /.widget -->
        </div>
        <!-- /column -->
      </div>
      <!--/.row -->
    </div>
    <div class="footer-end">
                            <p> <i class=" uil uil-copyright"></i>2023-24 {{$setting->short_name}}  - đang xây dựng    </p>
                        </div>
    <!-- /.container -->
  </footer> --}}

  <?php $detail = \App\Models\SettingDetail::find(1); ?>
                            
<footer class="dark-footer footer-style-1">
        <section class="section-b-space darken-layout">
            <div class="container">
                <div class="row footer-theme partition-f">
                    <div class="col-lg-4 col-md-6 sub-title">
                        <div class="footer-title footer-mobile-title">
                            <h4>Thông tin công ty</h4>
                        </div>
                        <div class="footer-contant">
                            <div class="footer-logo"><img src="{{$detail->logo}}" alt=""></div>
                            <h3>{{$detail->company_name}} </h3>
                            <p>{{$detail->memory}}</p>
                            <ul class="contact-list">
                             
                                <li><i class="fa fa-map-marker"></i>{{$detail->address}}
                                </li>
                                <li><i class="fa fa-phone"></i>
                                Điện thoại: {{$detail->phone}}</li>
                                <li><i class="fa fa-envelope"></i>Email: {{$detail->email}}</li>
                                <li><i class="fa fa-book"></i>Mã số doanh nghiệp: {{$detail->mst}}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="sub-title">
                            <div class="footer-title">
                                <h4>Link hữu ích</h4>
                            </div>
                            <div class="footer-contant">
                                <ul>
                                    <li><a href="">Chính sách bảo mật</a></li>
                                    <li><a href="">Điều khoản và quy định</a></li>
                                    <li><a href="">Chính sách hoàn trả</a></li>
                                    <li><a href="">Chính sách bảo hành</a></li>
                                    <li><a href="">Chính sách bảo vệ thông tin người dùng</a></li>

                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="sub-title">
                            <div class="footer-title">
                                <h4>Thông tin khác</h4>
                            </div>
                            <div class="footer-contant">
                                <ul>
                                    <li><a href="{{route('front.profile')}}">Cập nhật hồ sơ</a></li>
                                    <li><a href="">Giỏ hàng</a></li>
                                    <li><a href="#">Đơn hàng</a></li>
                                    <li><a href="#">Công nợ</a></li>
                                    <li><a href="">Liên hệ</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="sub-title">
                            <div class="footer-title">
                                <h4>Follow us</h4>
                            </div>
                            <div class="footer-contant">
            
                              
                              
                               <div class="footer-social">
                                    <ul>
                                         
                                        <li><a href="{{$detail->facebook}}"><img src="{{asset('frontend/assets/images/icon/facenho.png')}}" class=" "/> </a></li>
                                        <li><a href="{{$detail->shopee}}"><img src="{{asset('frontend/assets/images/icon/shopeenho.png')}}" class=" "/> </a></li>
                                        <li><a href="{{$detail->lazada}}"><img src="{{asset('frontend/assets/images/icon/laznho.png')}}" class=" "/> </a></li>
 
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="sub-footer dark-subfooter">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6 col-md-6 col-sm-12">
                    
                        <div class="footer-end">
                        
                            <p>
                           
                            <i class="fa fa-copyright" aria-hidden="true"></i> 2023-24 {{$detail->short_name}} - đang xây dựng</p>
                        </div>
                        
                    </div>
                    <div class="col-xl-6 col-md-6 col-sm-12">
                        <div class="payment-card-bottom">
                        <div id='bct' style="padding-top:5px;width:200px; height:auto;  ">
                                  
                                  <?php
                                           echo $detail->logo_bct ;
                                           ?>
                                           </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
       
       