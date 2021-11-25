@extends('layouts.app')

@section('title')
  Detail Surat Jalan
@endsection

@section('breadcrumb')
   @parent
   <li>Surat Jalan</li>
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
            <th>Stok Gudang</th>
            <th>Stok Toko</th>
            <th>Jumlah Kirim</th>
            <th>Jumlah Terima</th>
            <th>Tanggal Kadlauarsa</th>
          </tr>
        </thead>
        <tbody></tbody>
        </table>
      </div>
      <div class="box-footer">
        <a href="{{route('approve_terima_retur_toko.index')}}" ></a>
        <form action="{{route('approve_terima_toko.approve')}}" method="post">
          {{csrf_field()}}
          <input type="hidden" name="id" value="{{$id}}">
          <button type="submit" class='btn btn-success pull-right'>Approve</button>
          <a href="{{route('approve_terima_toko.reject',$id)}}" class='btn btn-danger'>Reject</a>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1;
$(function(){
  url = "{{route('approve_terima_toko.listDetail',$id)}}"
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