@extends('layouts.app')

@section('title')
  Daftar Terim Barang
@endsection

@section('breadcrumb')
   @parent
   <li>Terima Barang</li>
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
            <th>Kod Produk</th>
            <th>Nama Produk</th>
            <th>Jumlah Pembelian</th>
            <th>Jumlah Terima</th>
            <th>Expired</th>
          </tr>
        </thead>
        <tbody></tbody>
        </table>
      </div>
      <div class="box-footer">
        <a href="{{route('approve_terima_po.approve',$id)}}" class='btn btn-danger pull-right'>Prosess</a>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1;
$(function(){
  url = "{{route('approve_terima_po.listDetail',$id)}}"
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