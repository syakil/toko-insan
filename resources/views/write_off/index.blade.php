@extends('layouts.app')

@section('title')
  Write Off
@endsection

@section('header')
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
@endsection

@section('breadcrumb')
   @parent
   <li>Dashboard</li>
   <li>Write_off</li>
@endsection

@section('content')

  @if ($message = Session::get('error'))
    <div class="alert alert-danger alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button> 
      <strong>{{ $message }}</strong>
  </div>
  @endif 

  @if ($message = Session::get('success'))
    <div class="alert alert-success alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button> 
      <strong>{{ $message }}</strong>
  </div>
  @endif

<div class="row">
  <div class="col-xs-6">
    <div class="box">
      <div class="box-body">
      
      <div class="alert alert-warning alert-dismissible">
        <h4><i class="icon fa fa-warning"></i> Peringatan!</h4>
        Pastikan Barang Yang Ingin Write OFF Disetuji Oleh Pengurus KSPPS NUR INSANI ! <br>
        Data Yang Sudah Write OFF tidak Bisa Dikembalikan.
      </div>
      
        <form autocomplete="off" class="form-wo" action="{{route('write_off.store')}}" method="post">
        {{ csrf_field() }}
          <div class="form-group">
            <label for="cari">Produk</label>
            <input id="produk" type="text" class="form-control"  onClick="this.value = ''" name="produk" autofocus required>
            <div id="parentProdukList"></div>
          </div>
          <input type="hidden" name="kode" id="kode">
          
          <div class="form-group">
            <label for="jumlah">Stok Gudang</label>
            <input type="number" class="form-control" name="stok" id="stok" placeholder="Stok Gudang" readonly>
            <small class="form-text text-muted">Stok Gudang</small>
          </div>


          <div class="form-group">
            <label for="jumlah">Qty</label>
            <input type="number" class="form-control" name="jumlah" id="jumlah" placeholder="Masukan Qty" required>
            <small class="form-text text-muted">Masukan Qty Sesuai Fisik</small>
          </div>

          <div class="form-group">
            <label for="expired_date">Tanggal Kadaluarsa</label>
            <input type="date" class="form-control" name="expired_date" id="expired_date" placeholder="Expired Date" required>
            <small id="expired_date" class="form-text text-muted">Masukan Tanggal Kadaluarsa</small>
          </div>

          <button type="button" class="btn btn-primary pull-right proses">Proses</button>
        </form>

      </div>
    </div>
  </div>
</div>
@endsection


@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script type="text/javascript">
var url = "{{route('write_off.loadData')}}";

$('#produk').keyup(function(){
  var query = $(this).val();
  if (query.length > 3) {
    $.ajax({
      url : url,
      method:"get",
      data: {query:query},
      success: function(data){
          $('#parentProdukList').append('<div id="produkList"></div>');    
          $("#produkList").fadeIn();
          $("#produkList").html(data);
      }
    })
  }else{
    $('#produkList').fadeOut();  
  }
})

$(document).on('click', 'li.produk_list', function(){
  var text = $(this).text();
  var nilai = text.split(" ")
  var kode_produk = nilai[0];
  var nama = text.split(kode_produk)
  var nama_produk = text.split("-,")
  $('#produkList').remove();
  $('#produk').val(text);  
  $('#kode').val(kode_produk)

  var url_stok = "{{route('write_off.loadstok',':kode')}}";
  url_stok = url_stok.replace(':kode',kode_produk)
  $.ajax({
    url : url_stok,
    method:"get",
    success: function(data){
      $("#stok").val(data);
    }
  })

});  
</script>

<script>
$('form').on('focus', 'input[type=number]', function (e) {
  $(this).on('wheel.disableScroll', function (e) {
    e.preventDefault()
  })
})
$('form').on('blur', 'input[type=number]', function (e) {
  $(this).off('wheel.disableScroll')
})

$(document).ready(function() {
  $(window).keydown(function(event){
    if(event.keyCode == 13) {
      event.preventDefault();
      return false;
    }
  });
});

$('.proses').click(function(){
  
  var stok = $('#stok').val()
  var qty = $('#jumlah').val()
  console.log(qty);
  console.log(stok);
  if (parseInt(stok) < parseInt(qty)) {
    $('#jumlah').val('')
    alert('Jumlah Melebih Stok !')
  }else{
    $('.form-wo').submit();
  }
});
</script>
@endsection