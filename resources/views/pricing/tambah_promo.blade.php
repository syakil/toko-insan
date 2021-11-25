@extends('layouts.app')

@section('title')
  Tambah Barang Promo
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
        
      <form action="{{route('pricing.update_promo')}}"class="form-horizontal"  data-toggle="validator" method="post">
    {{ csrf_field() }} {{ method_field('POST') }}
        


  <input type="hidden" id="id" name="kode_produk" value="{{$produk->kode_produk}}">
  <div class="form-group">
    <label class="col-md-3 control-label">Kode Promo</label>
    <div class="col-md-6">
      <input id="kode_promo" type="number" class="form-control" name="kode_promo" value="123{{substr($produk->kode_produk,-7,7)}}" readonly>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  
  <div class="form-group">
    <label for="nama" class="col-md-3 control-label">Nama Produk</label>
    <div class="col-md-6">
      <input id="nama" type="text" class="form-control" name="nama_produk" value="PRO {{$produk->nama_produk}}">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="nama_struk" class="col-md-3 control-label">Nama Struk</label>
    <div class="col-md-6">
      <input id="nama_struk" type="text" class="form-control" name="nama_struk" value="PRO {{$produk->nama_struk}}">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_beli" class="col-md-3 control-label">Harga Beli</label>
    <div class="col-md-3">
      <input id="harga_beli" type="text" class="form-control" readonly name="harga_beli" value="{{$produk->harga_beli}}">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="stok" class="col-md-3 control-label">Stok</label>
    <div class="col-md-3">
      <input id="stok" type="number" class="form-control" name="stok" required>
      <span class="help-block with-errors"></span>
    </div>
  </div>

    
  <div class="form-group">
    <label for="harga_jual_insan" class="col-md-3 control-label">Harga Member Insan</label>
    <div class="col-md-3">
      <input id="harga_jual_insan" type="text" class="form-control" name="harga_jual_insan" id="harga_jual_insan" onkeyup="margin_ni2()" value="{{ $produk->harga_jual_insan }} "required>
      <span class="help-block with-errors"></span>
    </div>

    
    <label for="margin_ni" class="col-md-1 control-label">Margin NI</label>
    <div class="col-md-3">
      <input type="text" class="form-control" id="margin_ni" onkeyup="harga_ni()">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_jual" class="col-md-3 control-label">Harga Member Pabrik</label>
    <div class="col-md-3">
      <input id="harga_jual" type="text" value="{{$produk->harga_jual}}" class="form-control" name="harga_jual" onkeyup="margin_umum1()"required>
      <span class="help-block with-errors"></span>
    </div>

    
    <label for="margin_umum" class="col-md-1 control-label">Margin</label>
    <div class="col-md-3">
      <input type="text" class="form-control" id="margin_umum" onkeyup="harga_jual1()">
      <span class="help-block with-errors"></span>
    </div>
  </div>

   
   <div class="modal-footer">
      <button type="submit" class="btn btn-primary btn-save"><i class="fa fa-floppy-o"></i> Simpan </button>
      <a href="{{ route('pricing.index') }}" class="btn btn-warning "><i class="fa fa-arrow-circle-left"></i> <span>Batal</span></a>
   </div>
    
   </form>

      
      </div>
    </div>
  </div>
</div>


@endsection

@section('script')

<script>
function margin_umum1() {
    var harga_beli = $("#harga_beli").val();
    var harga_jual = $("#harga_jual").val();
    var margin_umum = (parseInt(harga_jual)-parseInt(harga_beli))/parseInt(harga_jual)*100;
    $("#margin_umum").val(margin_umum.toFixed(2));
}
</script>


<script>
function nilai_pajak() {
    var harga_beli_sebelum_pajak = $("#harga_beli_sebelum_pajak").val();
    var pajak = $("#pajak").val();
    var harga_beli = (parseInt(harga_beli_sebelum_pajak)*pajak/100)+parseInt(harga_beli_sebelum_pajak);
    $("#harga_beli").val(Math.round(harga_beli));
}
</script>


<script>
function harga_jual1() {
    var harga_beli = $("#harga_beli").val();
    var margin = $("#margin_umum").val();
    var harga_jual = (parseInt(harga_beli)*margin/100)+parseInt(harga_beli);
    $("#harga_jual").val(harga_jual);
}
</script>


<script>
function margin_ni2() {
    var harga_beli = $("#harga_beli").val();
    var harga_jual_insan = $("#harga_jual_insan").val();
    var margin_ni = (parseInt(harga_jual_insan)-parseInt(harga_beli))/parseInt(harga_jual_insan)*100;
    $("#margin_ni").val(margin_ni.toFixed(2));
}
</script>

<script>
function harga_ni() {
    var harga_beli = $("#harga_beli").val();
    var margin_ni = $("#margin_ni").val();
    var harga_jual_insan = (parseInt(harga_beli)*margin_ni/100)+parseInt(harga_beli);
    $("#harga_jual_insan").val(harga_jual_insan);
}
</script>


@endsection
