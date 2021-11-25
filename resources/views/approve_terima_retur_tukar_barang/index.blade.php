@extends('layouts.app')

@section('title')
  Daftar Retur Supplier
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
        <table class="table table-striped tabel-retur">
          <thead>
            <tr>
              <th width="30">No</th>
              <th>Tanggal</th>
              <th>Supplier</th>
              <th>Total Item</th>
              <th>Total Harga</th>
              <th width="100">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('approve_terima_retur_tukar_barang.detail')
@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1;
$(function(){
   table = $('.tabel-retur').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('approve_terima_retur_tukar_barang.data') }}",
       "type" : "GET"
     }
   }); 
   
    table1 = $('.tabel-detail').DataTable({
      "dom" : 'Brt',
      "bSort" : false,
      "processing" : true
    });
});


function showDetail(id){
  $('#modal-detail').modal('show');
  url = "{{route('approve_terima_retur_tukar_barang.show',':id')}}";
  url = url.replace(':id',id)
  
  url_approve = "{{route('approve_terima_retur_tukar_barang.approve',':id')}}"
  url_approve = url_approve.replace(':id',id)

  url_reject = "{{route('approve_terima_retur_tukar_barang.reject',':id')}}"
  url_reject = url_reject.replace(':id',id)

  $('#approve').attr('href',url_approve)
  $('#reject').attr('href',url_reject)
  table1.ajax.url(url);
  table1.ajax.reload();
}
</script>
@endsection
