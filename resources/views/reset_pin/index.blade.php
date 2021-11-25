@extends('layouts.app')

@section('title')
  Daftar Member
@endsection

@section('breadcrumb')
   @parent
   <li>Reset PIN</li>
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
        <table class="table table-striped tabel-member">
        <thead>
          <tr>
              <th>Unit</th>
              <th>Kode Kelompok</th>
              <th>Kode Member</th>
              <th>KTP/NIK</th>
              <th>Nama</th>
              <th>Tanggal Lahir</th>
              <th>Aksi</th>
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
var table;
$(function(){
   table = $('.tabel-member').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('reset_pin.data') }}",
       "type" : "GET"
     }
   }); 
});
</script>
@endsection