@extends('backend.layouts.master')
@section ('scriptop')

<meta name="csrf-token" content="{{ csrf_token() }}">
 

@endsection
@section('content')
<div>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            Thêm thành viên nhóm {{$group->title}}
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-12 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <!-- BEGIN: Form Layout -->
            <form method="post" action="{{ route('admin.groupmember.store') }}">
                @csrf
                <input type="hidden" name='group_id' value="{{$group->id}}"/>
                <div class="intro-y box p-5">
                    {{-- Error Handling --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
            
                    {{-- Title Input --}}
                    <div class=" mt-3 ">
                        <label style="min-width:50px  " class="form-select-label" for="">
                        Chọn khách hàng
                        </label>
                        <div class="flex">
                            <input type="text" id='customer_search' 
                                class="form-control py-3   " placeholder="Tên hoặc số điện thoại">
                           
                        </div>
                        <input type="hidden" id="user_id" name="user_id" value="0" />
                        
                    </div>
            
                    
                     {{-- Type Code Dropdown --}}
                     <div class="mt-3">
                        <label for="role" class="form-label">Vai trò</label>
                        <select id="role" name="role" class="form-select mt-2">
                           
                            @foreach($roles as $role)
                                <option value="{{ $role->type_code }}" {{ old('type_code') == $role->type_code ? 'selected' : '' }}>
                                    {{ $role->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

            
                    {{-- Status Select --}}
                    <div class="mt-3">
                        <label for="status" class="form-label">Tình trạng</label>
                        <select id="status" name="status" class="form-select mt-2">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
            
                    {{-- Submit Button --}}
                    <div class="text-right mt-5">
                        <button type="submit" class="btn btn-primary w-24">Lưu</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

 

@endsection

@section ('scripts')
<link href="https://code.jquery.com/ui/1.12.0/themes/smoothness/jquery-ui.css" rel="Stylesheet"> 
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js" ></script>
<script>
      $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });
    $(document).ready(function(){ 

    var customer_search = $('#customer_search');
        customer_search.autocomplete({
            source: function(request, response) {
                $.ajax({
                    type: 'GET',
                    url: '{{route('admin.user.jsearch')}}',
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

            $('#user_id').val(ui.item.id);
            
            }
        }).data('ui-autocomplete')._renderItem = function(ul, item){
            $( ul ).addClass('dropdown-content overflow-y-auto h-52 z-index:900000');
            return $("<li class='mt-10 dropdown-item  '></li>")
                .data("item.autocomplete", item )
                // .append('<div  style="clear:both"><div style="  pointer-events: none; width:50; float:left; "><img width="50" height="50" src="'+item.imgurl+'"/></div> <div style="float:left"> <span style=" pointer-events: none;">'+item.value+' </span> <br/> <span>số lượng: '+ item.qty +'</span> &nbsp;&nbsp;&nbsp;&nbsp; <span> giá: '+  Intl.NumberFormat('en-US').format(item.price)+'</div></div>' )
                .append('<table style=" border:none; background:none" > <tr><td>'
                +'<span   style="line-height:220%">'+ item.value +'</span></td></tr></table>')
                .appendTo(ul);
            };;
        //////////end product search /////////////////////////

    } );
    
</script>
@endsection