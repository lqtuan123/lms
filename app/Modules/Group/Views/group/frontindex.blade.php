@extends('frontend.layouts.master')
@section('content')
 
@include('frontend.layouts.page_title')
<section class=" " style="padding-top:15px">
      

 
                <!-- <div class="position-absolute top-10px lg-top-minus-80px left-minus-80px lg-left-15px opacity-1 w-350px z-index-1 d-none d-lg-inline-block"><img src="images/demo-medical-pattern.svg" alt="" data-bottom-top="transform: translateY(-50px)" data-top-bottom="transform: translateY(50px)"></div> -->
        <div class="container   " style ="padding-top :35px"> 
            
            <div class="row row-cols-1 row-cols-xl-4 row-cols-lg-3 row-cols-sm-2 justify-content-center" data-anime='{"el": "childs", "translateY": [15, 0], "perspective": [1200,1200], "scale": [1.05, 1], "rotateX": [30, 0], "opacity": [0,1], "duration": 800, "delay": 100, "staggervalue": 300, "easing": "easeOutQuad" }'> 
                
                @foreach ($groups as $group )
                <!-- start team member item -->
                <div  class="col team-style-06 mb-20px   ">
                    <div style="height:330px" class="d-flex flex-column p-15px pb-10px lg-p-20px text-center border-radius-6px bg-white box-shadow-quadruple-large position-relative">
                        <div class="position-relative">
                        <a href="{{ $group->getPageUrl($group->id)}}" class="d-inline-block position-relative">
                            <img class="object-fit-cover border-radius-6px w-170px h-60px mb-10px"
                                src="{{$group->photo}}" alt="">   
                            <span style="top:-20px" class="fs-13 fw-600 text-dark-gray alt-font lh-30 position-absolute   right-minus-70px border-radius-30px bg-yellow ps-15px pe-15px">
                                <i class="fa-solid fa-star"></i> 4.9
                            </span>
                        </a>
                    </div>
                    <a href="{{ $group->getPageUrl($group->id)}}" class="text-dark-gray fs-18 fw-700 mb-5px">{{$group->title}}</a>
                    <p  style="text-align:left">
                        <?php 
                        echo substr($group->description,0,150) ;
                        ?>
                    </p>
                            
                    </div>
                </div>
                <!-- end team member item --> 
                @endforeach
            </div>
                <!-- start pagination -->
            <div class="col-12 mt-5 mb-5 d-flex justify-content-center">
                {{$groups->links('vendor.pagination.simple_itcctv')}}
            </div>
        </div>
</section>

@endsection

@section('footscripts')
<link href="https://code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.css" rel="Stylesheet"> 
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
      // /////////province search//////////////////////
    ///////////////////////////////////////////////
    var province_search = $('#province_search');
    province_search.autocomplete({
        source: function(request, response) {
            $.ajax({
                type: 'GET',
                url: '{{route('front.province.jsearch')}}',
                data: {
                    data: request.term,
                },
                success: function(data) {
                    console.log(data);
                    response( jQuery.map( data.msg, function( item ) {
                        return {
                        id: item.id,
                        value: item.title,
                       
                        }
                    }));
                }
            });
        },
        response: function(event, ui) {
        
        },
        select: function(event, ui) {

           $('#province_id').val(ui.item.id);
           $('#district_id').val(0);
           $('#ward_id').val(0);
           $('#district_search').val('');
           $('#ward_search').val('');
           
        }
    }).data('ui-autocomplete')._renderItem = function(ul, item){
        $( ul ).addClass('dropdown-content overflow-y-auto h-52 ');
        return $("<li class='mt-10 dropdown-item  '></li>")
            .data("item.autocomplete", item )
            // .append('<div  style="clear:both"><div style="  pointer-events: none; width:50; float:left; "><img width="50" height="50" src="'+item.imgurl+'"/></div> <div style="float:left"> <span style=" pointer-events: none;">'+item.value+' </span> <br/> <span>số lượng: '+ item.qty +'</span> &nbsp;&nbsp;&nbsp;&nbsp; <span> giá: '+  Intl.NumberFormat('en-US').format(item.price)+'</div></div>' )
            .append('<table style=" border:none; background:none" > <tr><td>'
            +'<span   >'+ item.value +'</span></td></tr></table>')
            .appendTo(ul);
        };;
    //////////end product search /////////////////////////
  // /////////district search//////////////////////
    ///////////////////////////////////////////////
    var district_search = $('#district_search');
    district_search.autocomplete({
        source: function(request, response) {
            var province_id = $('#province_id').val();
            if(province_id == 0)
            {
                Swal.fire(
                            'Thiếu thông tin',
                            'Chưa chọn tỉnh!',
                            'error'
                        );
                return;
            }
            $.ajax({
                type: 'GET',
                url: '{{route('front.district.jsearch')}}',
                data: {
                    data: request.term,
                    province_id: province_id,
                },
                success: function(data) {
                    console.log(data);
                    response( jQuery.map( data.msg, function( item ) {
                        return {
                        id: item.id,
                        value: item.title,
                       
                        }
                    }));
                }
            });
        },
        response: function(event, ui) {
        
        },
        select: function(event, ui) {

           $('#district_id').val(ui.item.id);
           $('#ward_id').val(0);
           $('#ward_search').val('');
           
        }
    }).data('ui-autocomplete')._renderItem = function(ul, item){
        $( ul ).addClass('dropdown-content overflow-y-auto h-52 ');
        return $("<li class='mt-10 dropdown-item  '></li>")
            .data("item.autocomplete", item )
            // .append('<div  style="clear:both"><div style="  pointer-events: none; width:50; float:left; "><img width="50" height="50" src="'+item.imgurl+'"/></div> <div style="float:left"> <span style=" pointer-events: none;">'+item.value+' </span> <br/> <span>số lượng: '+ item.qty +'</span> &nbsp;&nbsp;&nbsp;&nbsp; <span> giá: '+  Intl.NumberFormat('en-US').format(item.price)+'</div></div>' )
            .append('<table style=" border:none; background:none" > <tr><td>'
            +'<span   style="line-height:220%">'+ item.value +'</span></td></tr></table>')
            .appendTo(ul);
        };;
    //////////end district search /////////////////////////
 
  // /////////ward search//////////////////////
    ///////////////////////////////////////////////
    var ward_search = $('#ward_search');
    ward_search.autocomplete({
        source: function(request, response) {
            var district_id = document.getElementById('district_id').value;
            // alert(district_id);
            if(district_id == 0)
            {
                Swal.fire(
                            'Thiếu thông tin',
                            'Chưa chọn quận/huyện!',
                            'error'
                        );
                return;
            }
            $.ajax({
                type: 'GET',
                url: '{{route('front.ward.jsearch')}}',
                data: {
                    data: request.term,
                    district_id: district_id,
                },
                success: function(data) {
                    console.log(data);
                    response( jQuery.map( data.msg, function( item ) {
                        return {
                        id: item.id,
                        value: item.title,
                       
                        }
                    }));
                }
            });
        },
        response: function(event, ui) {
        
        },
        select: function(event, ui) {

           $('#ward_id').val(ui.item.id);
           
        }
    }).data('ui-autocomplete')._renderItem = function(ul, item){
        $( ul ).addClass('dropdown-content overflow-y-auto h-52 ');
        return $("<li class='mt-10 dropdown-item  '></li>")
            .data("item.autocomplete", item )
            // .append('<div  style="clear:both"><div style="  pointer-events: none; width:50; float:left; "><img width="50" height="50" src="'+item.imgurl+'"/></div> <div style="float:left"> <span style=" pointer-events: none;">'+item.value+' </span> <br/> <span>số lượng: '+ item.qty +'</span> &nbsp;&nbsp;&nbsp;&nbsp; <span> giá: '+  Intl.NumberFormat('en-US').format(item.price)+'</div></div>' )
            .append('<table style=" border:none; background:none" > <tr><td>'
            +'<span    >'+ item.value +'</span></td></tr></table>')
            .appendTo(ul);
        };;
   
    //////////end ward search /////////////////////////
</script>
@endsection