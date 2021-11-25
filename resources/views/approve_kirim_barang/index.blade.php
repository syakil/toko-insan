@extends('layouts.app')

@section('title')
  Daftar Surat Jalan
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
      <div class="box-header">
      </div>
      <div class="box-body">  
      <table class="table table-striped tabel-kirim">
      <thead>
        <tr>
            <th width="30">No</th>
            <th>No Po</th>
            <th>Tanggal</th>
            <th>Unit</th>
            <th>Status</th>
            <th>Total Item</th>
            <th width="100">Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
      </table>
      </div>
    </div>
  </div>
</div>


@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1,list;
$(function(){
   table = $('.tabel-kirim').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('approve_kirim_barang.data') }}",
       "type" : "GET"
     }
   }); 
});
</script>
@endsection
