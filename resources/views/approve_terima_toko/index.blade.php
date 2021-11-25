@extends('layouts.app')

@section('title')
  Daftar Surat Jalan
@endsection

@section('breadcrumb')
   @parent
   <li>pembelian</li>
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
        <table class="table table-striped tabel-pembelian">
          <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal</th>
                <th>No. Surat Jalan</th>
                <th>Unit</th>
                <th>Total Kirim</th>
                <th>Total Terima</th>
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
var table, save_method, table1;
$(function(){
  table = $('.tabel-pembelian').DataTable({
    "processing" : true,
    "serverside" : true,
    "responsive" : true,
    "ajax" : {
      "url" : "{{ route('approve_terima_toko.data') }}",
      "type" : "GET"
    }
  }); 
});
</script>
@endsection