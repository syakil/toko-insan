@extends('layouts.app')

@section('title')
  Daftar Kirim Barang
@endsection

@section('breadcrumb')
   @parent
   <li>Kirim Barang</li>
@endsection

@section('content')     


@if ($message = Session::get('error'))
  <script>
    var pesan = "{{$message}}"
    swal("Maaf !", pesan, "error"); 
  </script>
@elseif ($message = Session::get('success'))
  <script>
    var pesan = "{{$message}}"
    swal("Selamat !", pesan, "success"); 
  </script>
@endif

<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-body">  
        <table class="table table-striped tabel-terima">
        <thead>
          <tr>
            <th width="30">No</th>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Stok Gudang</th>
            <th>Jumlah Kirim</th>
            <th>Expired</th>
          </tr>
        </thead>
        <tbody></tbody>
        </table>
      </div>
      <div class="box-footer">
        <form action="{{route('approve_kirim_barang.approve')}}" method="post">
          {{csrf_field()}}
          <input type="hidden" name="id" value="{{$id}}">
          <button type="submit" class='btn btn-success pull-right' >Approve</button>
        </form>
        <a href="{{route('approve_kirim_barang.reject',$id)}}" class='btn btn-danger pull-right' style="margin-right: 10px;"  >Tolak</a>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1;
$(function(){
  url = "{{route('approve_kirim_barang.listDetail',$id)}}"
  // console.log("{{$id}}");
  table = $('.tabel-terima').DataTable({
    "processing" : true,
    "serverside" : true,
    "responsive" : true,
    "ajax" : {
      "url" : url,
      "type" : "GET"
    }
  }); 
});
</script>
@endsection