@extends('layouts.app')

@section('title')
  Tambah Produk
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
        
      <form action="/toko/pricing/add "class="form-horizontal"  data-toggle="validator" method="post">
    {{ csrf_field() }} {{ method_field('POST') }}
        


  <input type="hidden" id="id" name="id" value="">
  <div class="form-group">
    <label for="kode" class="col-md-3 control-label">Kode Produk</label>
    <div class="col-md-6">
      <input id="kode" type="number" class="form-control" name="kode_produk" value="" >
      <span class="help-block with-errors"></span>
    </div>
  </div>

  
  <div class="form-group">
    <label for="nama" class="col-md-3 control-label">Nama Produk</label>
    <div class="col-md-6">
      <input id="nama" type="text" class="form-control" name="nama_produk" value="">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="nama_struk" class="col-md-3 control-label">Nama Struk</label>
    <div class="col-md-6">
      <input id="nama_struk" type="text" class="form-control" name="nama_struk" value="">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="kategori" class="col-md-3 control-label">Status</label>
    <div class="col-md-6">
      <select id="kategori" type="text" class="form-control" name="status" required>
        @foreach($param as $list)
        <option value="{{ $list->id_status }}">{{ $list->keterangan }}</option>
        @endforeach
      </select>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_beli_sebelum_pajak" class="col-md-3 control-label">Harga Beli</label>
    <div class="col-md-3">
      <input id="harga_beli_sebelum_pajak" type="text" class="form-control" name="harga_beli_sebelum_pajak" value="">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="diskon" class="col-md-3 control-label">Diskon</label>
    <div class="col-md-3">
      <input id="diskon" type="number" class="form-control" name="diskon" value="required>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">    
    <label for="pajak" class="col-md-3 control-label">Pajak</label>
    <div class="col-md-3">
      <input type="text" class="form-control" id="pajak" onkeyup="nilai_pajak()" value="0">
      <span class="help-block with-errors"></span>
    </div>

    
    <label for="harga_beli" class="col-md-1 control-label">HPP</label>
    <div class="col-md-3">
      <input type="text" class="form-control" id="harga_beli" name="harga_beli">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_jual_insan" class="col-md-3 control-label">Harga Member Insan</label>
    <div class="col-md-3">
      <input id="harga_jual_insan" type="text" class="form-control" name="harga_jual_insan" id="harga_jual_insan" onkeyup="margin_ni2()" required>
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
      <input id="harga_jual" type="text" class="form-control" name="harga_jual" onkeyup="margin_umum1()"required>
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