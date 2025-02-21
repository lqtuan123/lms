<?php
   $detail = \App\Models\SettingDetail::find(1);
  
  $keyword = '';
  $description='';
?>
<meta charset="utf-8">
<link href="{{$detail?$detail->icon:''}}" rel="shortcut icon">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="GENERATOR" content="{{$detail?$detail->short_name:''}}" />
<meta name="keywords" content= "{{isset($keyword)?$keyword:$detail->keyword}}"/>
<meta name="description" content= "{{isset($description)?strip_tags($description):$detail->memory}}"/>
<meta name="author" content="{{$detail?$detail->short_name:''}}">
<title>{{$detail?$detail->company_name:''}}</title>
<!-- BEGIN: CSS Assets-->
<link rel="stylesheet" href="{{asset('backend/css/app.css')}}" />
<link rel="stylesheet" href="{{asset('backend/css/bootstrap-switch-button.min.css')}}" > 
 

@yield('css')
@yield('scriptop')