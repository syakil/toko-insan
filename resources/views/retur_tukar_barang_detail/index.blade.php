@extends('layouts.app')

@section('title')
 Surat Jalan
@endsection

@section('breadcrumb')
   @parent
   <li>Surat Jalan</li>
   <li>tambah</li>
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

<table>
  <tr><td width="150">Toko</td><td><b>{{ $supplier->nama }}</b></td></tr>
  <tr><td>Alamat</td><td><b>{{ $supplier->alamat }}</b></td></tr>
  <tr><td>Kode Toko</td><td><b>{{ $supplier->id_supplier }}</b></td></tr>
  <tr><td>No Surat Jalan</td><td><b>{{ $idpembelian }}</b></td></tr>
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
    <th>Stok WO</th>
    <th>Jumlah</th>
    <th width="100">Aksi</th>
  </tr>
</thead>
<tbody></tbody>
</table>
</form>

  <!-- <div class="col-md-8">
     <div id="tampil-bayar" style="background: #dd4b39; color: #fff; font-size: 80px; text-align: center; height: 100px"></div>
     <div id="tampil-terbilang" style="background: #3c8dbc; color: #fff; font-weight: bold; padding: 10px"></div>
  </div> -->
  <div class="col-md-8"></div>
  <div class="col-md-4">
    <form class="form form-horizontal form-pembelian" method="post" action="{{  route('retur_tukar_barang.store') }} ">
      {{ csrf_field() }}
      <input type="hidden" name="idpembelian" value="{{ $idpembelian }}">
      <input type="hidden" name="total" id="total">
      <!-- <input type="hidden" name="totalitem" id="totalitem"> -->
      <input type="hidden" name="bayar" id="bayar">

      <div class="form-group">
        <label for="totalitem" class="col-md-4 control-label">Total Item</label>
        <div class="col-md-8">
          <input type="text" class="form-control" id="totalitem" readonly>
        </div>
      </div>

    </form>
  </div>

      </div>
      
      <div class="box-footer">
      <div class="pull-right">
        <button type="submit" class="btn btn-success simpan" ><i class="fa fa-print"></i> Proses Transaksi</button>
        <a href="{{route('retur_tukar_barang.hold',$idpembelian)}}" class="btn btn-warning"><i class="fa fa-floppy-o"></i> Hold</a> 
      </div>
      </div>
    </div>
  </div>
</div>

@include('retur_tukar_barang_detail.produk')
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
      "scrollY" : "200px",
      "paging": false,
     "ajax" : {
       "url" : "{{ route('retur_tukar_barang_detail.data', $idpembelian) }}",
       "type" : "GET"
     }
  }).on('draw.dt', function(){
    loadForm("{{$idpembelian}}");
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
   $('.simpan').click(function(){
      $('.form-pembelian').submit();
   });
});
function addItem(){
  $.ajax({
    url : "{{ route('retur_tukar_barang_detail.store') }}",
    type : "POST",
    data : $('.form-produk').serialize(),
    success : function(data){
      
      if(data.alert){
        alert(data.alert);
      }
      $('#kode').val('').focus(); 
      table.ajax.reload(function(){
      loadForm("{{$idpembelian}}");
      });             
    },
    error : function(){
       alert("Tidak dapat menyimpan data!");
    }   
  });
}
function selectItem(kode){
  $('#kode').val(kode);
  $('#modal-produk').modal('hide');
  addItem();
}

function changeCount(id){
  var url = "{{route('retur_tukar_barang_detail.update',':id')}}";
  url = url.replace(':id', id);
     $.ajax({
        url : url,
        type : "GET",
        data : $('.form-keranjang').serialize(),
        success : function(data){
          if(data.alert){
            alert(data.alert);
          }
          table.ajax.reload(function(){
            loadForm("{{$idpembelian}}");
          });
        },
        error : function(){
          alert("Tidak dapat menyimpan data!");
        }   
     });
}

function showProduct(){
  $('#modal-produk').modal('show');
}
function deleteItem(id){
  
  var url = "{{route('retur_tukar_barang_detail.destroy',':id')}}";
  url = url.replace(':id', id);

   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : url,
       type : "POST",
       data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
       success : function(data){
         table.ajax.reload(function(){
            loadForm("{{$idpembelian}}");
          }); 
       },
       error : function(){
         alert("Tidak dapat menghapus data!");
       }
     });
   }
}
function loadForm(id){
  var url = "{{route('retur_tukar_barang_detail.loadForm',':id')}}";
  url = url.replace(':id', id);
  $.ajax({
       url : url,
       type : "GET",
       dataType : 'JSON',
       success : function(data){
         $('#totalitem').val(data.totalitem);
       },
       error : function(){
         alert("Tidak dapat menampilkan data!");
       }
  });
}

</script>

@endsection

