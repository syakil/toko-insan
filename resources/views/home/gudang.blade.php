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


@if($errors->any())
<div class="col-md-10">

              <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> Alert!</h4>
                Belum Waktunya Stok Opname <br>
                <small><i>Silahkan Hubungi Bagian IT</i></small>

              </div>
              
</div>
@endif

  <div class="col-xs-12">
    <div class="box">
       <div class="box-body text-center">
            <h1>Selamat Datang</h1>
            <h2>Anda login sebagai Admin Gudang</h2>
                  
      </div>
   </div>
  </div>

  <a href="#" class="ubah-tanggal" data-toggle="modal" data-target="#exampleModal">
<div class="col-md-4"></div>
<div class="col-md-4 col-sm-6 col-xs-12 text-center">
  <div class="info-box bg-blue">
    <span class="info-box-icon"><i class="fa fa-calendar"></i></span>

    <div class="info-box-content">
      <br>
      <span class="info-box-text">Tanggal Transaksi</span>
      <span class="angka">{{$param_tgl}}</span>

    </div>
    <!-- /.info-box-content -->
  </div>
  <!-- /.info-box -->
</div>
<!-- /.col -->
</a>
</div>

<input type="hidden" value="{{$cek}}" id="cek">

<div class="modal" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pengaturan Tanggal</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      <form action="{{route('home.store')}}" class="tanggal" method="post">
      {{ csrf_field() }}
        <div class="form-group">
          <label for="tanggal">Tanggal Transaksi</label>
          <input type="date" class="form-control" id="tanggal" name="tanggal" required>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </form>
      </div>
    </div>
  </div>
</div>

@endsection


@section('script')

<script>

var param ,

param = $("#cek").val();

if (param == 0) {
  $(".modal").modal('show');
}else{
  $(".modal").modal('hide');
}

</script>


<script>
$(".ubah-tanggal").on("click",function(){
  $(".tanggal").attr("action","{{ route('home.update', Auth::user()->id) }}")
})


</script>

@endsection
