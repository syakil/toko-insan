@extends('layouts.app')

@section('title')
  Daftar Produk
@endsection

@section('breadcrumb')
   @parent
   <li>produk</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
        <a onclick="addForm()" class="btn btn-success"><i class="fa fa-plus-circle"></i> Tambah</a>
        <a onclick="deleteAll()" class="btn btn-danger"><i class="fa fa-trash"></i> Hapus</a>
        <a onclick="printBarcode()" class="btn btn-info"><i class="fa fa-barcode"></i> Cetak Barcode</a>
      </div>
      <div class="box-body">  

<form method="post" id="form-produk">
{{ csrf_field() }}
<table class="table table-striped">
<thead>
   <tr>
      <th width="20"><input type="checkbox" value="1" id="select-all"></th>
      <th width="20">No</th>
      <th>Kode Produk</th>
      <th>Nama Produk</th>
      <th>Kategori</th>
      <th>Unit</th>
      <th>Harga Beli</th>
qewqewqweq      <th>Harga Jual</th>
      <th>Harga Insan</th>
      <th>Diskon</th>
      <th>Stok</th>
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

@include('produk.form')
@endsection

@section('script')
<script type="text/javascript">
var table, save_method;
$(function(){
  table = $('.table').DataTable({
    "processing" : true,
    "serverside" : true,
    "ajax" : {
      "url" : "{{ route('produk.data') }}",
      "type" : "GET"
    },
  }); 
});

function addForm(){
   save_method = "add";
   $('input[name=_method]').val('POST');
   $('#modal-form').modal('show');
   $('#modal-form form')[0].reset();            
   $('.modal-title').text('Tambah Produk');
   $('#kode').attr('readonly', false);
}

function editForm(id){
    save_method = "edit";
    $('input[name=_method]').val('PATCH');
    $('#modal-form form')[0].reset();
    $.ajax({
      url : "produk/"+id+"/edit",
      type : "GET",
      dataType : "JSON",
      success : function(data){
        $('#modal-form').modal('show');
        $('.modal-title').text('Edit Produk');
        $('#form_produk').attr("action", "/toko-master/produk/update/"+id);
        $('#id').val(data.id_produk);
        $('#kode').val(data.kode_produk).attr('readonly', true);
        $('#nama').val(data.nama_produk);
        $('#kategori').val(data.id_kategori);
        $('#merk').val(data.merk);
        $('#harga_beli').val(data.harga_beli);
        $('#diskon').val(data.diskon);
        $('#harga_jual').val(data.harga_jual);
        $('#harga_jual_insan').val(data.harga_jual_insan);
        $('#harga_jual_pabrik').val(data.harga_jual_pabrik);       
        $('#stok').val(data.stok);
        
      },
      error : function(){
        alert("Tidak dapat menampilkan data!");
      }
  });
}

function deleteData(id){
  if(confirm("Apakah yakin data akan dihapus?")){
      $.ajax({
        url : "produk/"+id,
        type : "POST",
        data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
        success : function(data){
        table.ajax.reload();
        },
        error : function(){
        alert("Tidak dapat menghapus data!");
        }
      });
    }
}

function deleteAll(){
  if($('input:checked').length < 1){
    alert('Pilih data yang akan dihapus!');
  }else if(confirm("Apakah yakin akan menghapus semua data terpilih?")){
      $.ajax({
        url : "produk/hapus",
        type : "POST",
        data : $('#form-produk').serialize(),
        success : function(data){
        table.ajax.reload();
      },
        error : function(){
        alert("Tidak dapat menghapus data!");
        }
      });
    }
}

function printBarcode(){
  if($('input:checked').length < 1){
    alert('Pilih data yang akan dicetak!');
  }else{
    $('#form-produk').attr('target', '_blank').attr('action', "produk/cetak").submit();
  }
}
</script>

<!-- <script type="text/javascript">
		function total() {
		var competitor1 = parseInt(document.getElementById('competitor1').value);
		var competitor2 = parseInt(document.getElementById('competitor2').value);
		var competitor3 = parseInt(document.getElementById('competitor3').value);
		var margin = parseInt(document.getElementById('margin').value);
		var jumlah_harga = (competitor1+competitor2+competitor3)/3;
    var avg = jumlah_harga + (jumlah_harga*margin/100);
		document.getElementById('avg').value = avg;
		}
		
		</script> -->

@endsection