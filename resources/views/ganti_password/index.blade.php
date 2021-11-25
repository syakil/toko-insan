@extends('layouts.app')

@section('title')
  Ganti Password
@endsection

@section('breadcrumb')
   @parent
   <li>Dashboard</li>
@endsection

@section('content') 
@if ($message = Session::get('error'))
  <div class="alert alert-danger alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button> 
    <strong>{{ $message }}</strong>
</div>
@endif 
@if ($message = Session::get('success'))
  <div class="alert alert-success alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button> 
    <strong>{{ $message }}</strong>
</div>
@endif
<div class="row">
  <div class="col-xs-12">
  <div class="box">
        <div class="box-header with-border">
          
        </div>
        <div class="box-body">
         
        <form action="{{route('ganti_password.update')}}" method="post">
        {{ csrf_field() }}
          <div class="form-group">
            <label for="password_lama">Password Lama</label>
            <input type="password" class="form-control" id="password_lama" name="password_lama">
          </div>
          <div class="form-group">
            <label for="password_baru">Password Baru</label>
            <input type="password" class="form-control" name="password_baru" id="password_baru">
            <input type="checkbox" onclick="myFunction()">Show Password 
          </div>
          <div class="form-group">
            <label for="password_konf">Konfirmasi Password</label>
            <input type="password" class="form-control" name="password_konf" id="password_konf">
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
         

        </div>
        <!-- /.box-body -->
       
        <!-- /.box-footer-->
      </div>
  </div>
</div>
@endsection


@section('script')

function myFunction() {
  var x = document.getElementById("password_baru");
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
} 
@endsection