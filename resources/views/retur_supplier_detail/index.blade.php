@extends('layouts.app')

@section('title')
 Surat Keluar
@endsection

@section('breadcrumb')
   @parent
   <li>Surat Keluar</li>
   <li>tambah</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">
    <div class="box">
    <div class="box-header">
    
    <!-- pesan error -->
    @if ($message = Session::get('error'))
      <div class="alert alert-danger alert-block">
        <button type="button" class="close" data-dismiss="alert">×</button> 
        <strong>{{ $message }}</strong>
      </div>
    @endif
    
    
    </div>
   
     <div class="box-body">

<table>
  <tr><td width="150">Supplier</td><td><b>{{ $supplier->nama }}</b></td></tr>
  <tr><td>Alamat</td><td><b>{{ $supplier->alamat_supplier }}</b></td></tr>
  <tr><td>Telpon</td><td><b>{{ $supplier->telpon }}</b></td></tr>
  <tr><td>Id Supplier</td><td><b>{{ $supplier->id_supplier }}</b></td></tr>
</table>
<hr>

<form class="form form-horizontal form-produk" method="post">
{{ csrf_field() }}
  <input type="hidden" name="idpembelian" value="{{ $idpembelian }}">
  <div class="form-group">
      <label for="kode" class="col-md-2 control-label">Kode Produk</label>
      <div class="col-md-5">
        <div class="input-group">
          <input id="kode" type="text" class="form-control" name="kode" autofocus required>
          <span class="input-group-btn">
            <button onclick="showProduct()" type="button" class="btn btn-info">...</button>
          </span>
        </div>
      </div>
  </div>
</form>

<form class="form-keranjang">
{{ csrf_field() }} {{ method_field('PATCH') }}
<table class="table table-striped tabel-pembelian">
<thead>
   <tr>
      <th width="30">No</th>
      <th>Kode Produk</th>
      <th>Nama Produk</th>
      <th>Jumlah</th>
      <th align="right">Expired Date</th>
      <th width="100">Aksi</th>
   </tr>
</thead>
<tbody></tbody>
</table>
</form>

  <!-- <div class="col-md-8"> -->
     <!-- <div id="tampil-bayar" style="background: #dd4b39; color: #fff; font-size: 80px; text-align: center; height: 100px"></div>
     <div id="tampil-terbilang" style="background: #3c8dbc; color: #fff; font-weight: bold; padding: 10px"></div> -->
  <!-- </div> -->
  <div class="col-md-4">
    <form class="form form-horizontal form-pembelian" method="post" action="{{  route('retur_supplier.store') }} ">
      {{ csrf_field() }}
      <input type="hidden" name="idpembelian" value="{{ $idpembelian }}">
      <input type="hidden" name="total" id="total">
      <input type="hidden" name="totalitem" id="totalitem">
      <input type="hidden" name="bayar" id="bayar">

      <!-- <div class="form-group">
        <label for="totalrp" class="col-md-4 control-label">Total</label>
        <div class="col-md-8">
          <input type="text" class="form-control" id="totalrp" readonly>
        </div>
      </div>

      <div class="form-group">
        <label for="diskon" class="col-md-4 control-label">Diskon</label>
        <div class="col-md-8">
          <input type="number" class="form-control" id="diskon" name="diskon" value="0">
        </div>
      </div>

      <div class="form-group">
        <label for="bayarrp" class="col-md-4 control-label">Bayar</label>
        <div class="col-md-8">
          <input type="text" class="form-control" id="bayarrp" readonly>
        </div>
      </div> -->

  <!-- </div> -->

      </div>
      
      <div class="box-footer">
        <button type="submit" class="btn btn-primary pull-right simpan"><i class="fa fa-floppy-o"></i> Simpan Transaksi</button>
    </form>
      </div>
    </div>
  </div>
</div>

@include('retur_supplier_detail.produk')
@endsection

@section('script')
<script type="text/javascript">
var table;
$(function(){
  $('.tabel-produk').DataTable();
  table = $('.tabel-pembelian').DataTable({
      "dom" : 'Brt',
      "bSort" : false,
      "processing" : true,
      "scrollY" : "500px",
      "paging" : false,
     "ajax" : {
       "url" : "{{ route('retur_supplier_detail.data', $idpembelian) }}",
       "type" : "GET"
     }
  }).on('draw.dt', function(){
    loadForm($('#diskon').val());
  });
  $('.form-produk').on('submit', function(e){
      return false;
   });
   $('#kode').change(function(){
      addItem();
   });
   $('.form-keranjang').submit(function(){
     return false;
   });
   $('#diskon').change(function(){
      if($(this).val() == "") $(this).val(0).select();
      loadForm($(this).val());
   });
   $('.simpan').click(function(){
      $('.form-pembelian').submit();
   });
});
function addItem(){
  $.ajax({
    url : "{{ route('retur_supplier_detail.store') }}",
    type : "POST",
    data : $('.form-produk').serialize(),
    success : function(data){
      $('#kode').val('').focus();
      table.ajax.reload(function(){
        loadForm($('#diskon').val());
      });             
    },
    error : function(){
       alert("Tidak dapat menyimpan data!");
    }   
  });
}
function selectItem(kode){
  $('#kode').val(kode);
  // console.log(kode);
  $('#modal-retur-produk').modal('hide');
  addItem();
}
function changeCount(id){
     $.ajax({
        url : "retur_supplier_detail/"+id,
        type : "POST",
        data : $('.form-keranjang').serialize(),
        success : function(data){
          $('#kode').focus();
          table.ajax.reload(function(){
            loadForm($('#diskon').val());
          });             
        },
        error : function(){
          alert("Tidak dapat menyimpan data!");
        }   
     });
}
function showProduct(){
  $('#modal-retur-produk').modal('show');
}
function deleteItem(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "retur_supplier_detail/"+id,
       type : "POST",
       data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
       success : function(data){
         table.ajax.reload(function(){
            loadForm($('#diskon').val());
          }); 
       },
       error : function(){
         alert("Tidak dapat menghapus data!");
       }
     });
   }
}
function loadForm(diskon=0){
  $('#total').val($('.total').text());
  $('#totalitem').val($('.totalitem').text());
  $.ajax({
       url : "retur_supplier_detail/loadform/"+diskon+"/"+$('.total').text(),
       type : "GET",
       dataType : 'JSON',
       success : function(data){
         $('#totalrp').val("Rp. "+data.totalrp);
         $('#bayarrp').val("Rp. "+data.bayarrp);
         $('#bayar').val(data.bayar);
         $('#tampil-bayar').text("Rp. "+data.bayarrp);
         $('#tampil-terbilang').text(data.terbilang);
       },
       error : function(){
         alert("Tidak dapat menampilkan data!");
       }
  });
}
</script>

@endsection