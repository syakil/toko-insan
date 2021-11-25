@extends('layouts.app')

@section('title')
  Daftar Produk Baru
@endsection

@section('breadcrumb')
   @parent
   <li>produk</li>
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
        <form method="post" id="form-produk">
          {{ csrf_field() }}
          <table class="table table-striped">
            <thead>
              <tr>
                <th width="20">No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Nama Struk</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Status</th>
                <th>Supplier</th>
                <th width="100">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
var table, save_method;
$(function(){
  table = $('.table').DataTable({
    "processing" : true,
    "serverside" : true,
    "ajax" : {
      "url" : "{{ route('approve_produk.data') }}",
      "type" : "GET"
    },
  }); 
});

function addForm(){
  $('#modal-form').modal('show');
  $('#modal-form form')[0].reset();            
}


function printBarcode(){
  if($('input:checked').length < 1){
    alert('Pilih data yang akan dicetak!');
  }else{
    $('#form-produk').attr('target', '_blank').attr('action', "produk/cetak").submit();
  }
}
</script>

@endsection