@extends('layouts.app')

@section('title')
  Report Produk Write OFF
@endsection

@section('breadcrumb')
   @parent
   <li>pembelian</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">
  
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

    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body">  

<table class="table table-striped tabel-pembelian">
<thead>
   <tr>
      <th width="30">No</th>
      <th>Tanggal Input</th>
      <th>Kode Produk </th>
      <th>Nama Produk</th>
      <th>Qty</th>
      <th>Total Harga Jual</th>
      <th>Total Harga Beli</th>
      <th>Status</th>
      <th width="100">Aksi</th>
   </tr>
</thead>
<tbody></tbody>
</table>

      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade bd-example-modal-lg" id="preview" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
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
     "ajax" : {
       "url" : "{{ route('write_off.listReport') }}",
       "type" : "GET"
     }
   }); 
});

function openModal(id){
  $('#modalUpload').modal('show');
  $('#id_wo').val(id);
}

function showDetail(id){
  url = "{{route('write_off.file',':id')}}"
  url = url.replace(':id',id)
  $.ajax({
  url: url,
  method: "GET",
  success: function (data) {
    window.open(data,'_blank')
  }
});
}

</script>
@endsection
