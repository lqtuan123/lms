<?php
 
  $setting =\app\Models\SettingDetail::find(1);
  $user = auth()->user();

?>
@extends('frontend.layouts.master')
@section('head_css')
@endsection
@section('content')
@include('frontend.layouts.book')
@endsection

