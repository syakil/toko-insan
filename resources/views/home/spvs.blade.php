@extends('layouts.app')

@section('title')
  Baranda
@endsection

@section('breadcrumb')
   @parent  
   <li>Dashboard</li>
@endsection

@section('content') 
<div class="row">
  <div class="col-xs-12">
    <div class="box">
       <div class="box-body text-center">
            <h1>Selamat Datang</h1>
            <h2>Anda login sebagai Supervisor Toko</h2>
                  
      </div>
   </div>
  </div>
</div>
@endsection