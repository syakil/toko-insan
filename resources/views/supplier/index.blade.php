@extends('layouts.app')

@section('title')
  Daftar Supplier
@endsection

@section('breadcrumb')
   @parent
   <li>supplier</li>
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
        <a onclick="addForm()" class="btn btn-success"><i class="fa fa-plus-circle"></i> Tambah</a>
      </div>
      <div class="box-body">  

<table class="table table-striped supplier">
<thead>
   <tr>
      <th width="30">No</th>
      <th>Nama Supplier</th>
      <th>Alamat</th>
      <th>Telpon</th>
      <th>PIC</th>
      <th>No Rekening</th>
      <th>Bank</th>
      <th>Metode Bayar</th>
      <th width="100">Aksi</th>
   </tr>
</thead>
<tbody></tbody>
</table>

      </div>
    </div>
  </div>
</div>

@include('supplier.form')
@endsection

@section('script')

<script type="text/javascript">
var table, save_method;

$(document).ready(function() {
  table = $('.supplier').DataTable({
    "processing" : true,
    "ajax" : {
      "url" : "{{ route('supplier.data') }}",
      "type" : "GET"
    }
  })  
   
  
}); 

function addForm(){
   $('#modal-form').modal('show');
   $('#form_action').attr('action', "{{route('supplier.tambah')}}")
   $('#modal-form form')[0].reset();            
   $('.modal-title').text('Tambah Supplier');
}

function editForm(id){
  $('#modal-form form')[0].reset();   
  url = "{{route('supplier.edit',':id')}}";
  url = url.replace(':id',id);
  $.ajax({
    url : url,
    type : "GET",
    dataType : "JSON",
    success : function(data){
      $('#modal-form').modal('show');
      $('.modal-title').text('Edit Supplier');
      link = "{{route('supplier.update_supplier',':id')}}";
      link = link.replace(':id',id);
      $('#form_action').attr('action', link);
      $('#id').val(data.id_supplier);
      $('#nama').val(data.nama);
      $('#alamat').val(data.alamat);
      $('#telepon').val(data.telpon);
      $('#pic').val(data.pic);
      $('#norek').val(data.norek);
      $('#nama_rek').val(data.nama_rek);
      $('#bank').val(data.bank);
      $('#metode').val(data.metode);     
    },
    error : function(){
      alert("Tidak dapat menampilkan data!");
    }
  });
}

</script>
@endsection