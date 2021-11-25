@extends('layouts.app')

@section('title')
  Edit Harga 
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
      <div class="box-header">
        
    <form action="{{route('pricing_kompetitor.update')}}" class="form-horizontal" method="post">
    {{ csrf_field() }} 
        


  <input type="hidden" id="id" name="id" value="{{$produk->id_produk}}">
  <div class="form-group">
    <label for="kode" class="col-md-3 control-label">Kode Produk</label>
    <div class="col-md-6">
      <input id="kode" type="number" class="form-control" name="kode" value="{{$produk->kode_produk}}" readonly>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  
  <div class="form-group">
    <label for="nama" class="col-md-3 control-label">Nama Produk</label>
    <div class="col-md-6">
      <input id="nama" type="text" class="form-control" name="nama_produk" value="{{$produk->nama_produk}}" readonly>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="nama_struk" class="col-md-3 control-label">Nama Struk</label>
    <div class="col-md-6">
      <input id="nama_struk" type="text" class="form-control" name="nama_struk" value="{{$produk->nama_struk}}" readonly>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_beli_sebelum_pajak" class="col-md-3 control-label">Harga Beli</label>
    <div class="col-md-3">
      <input id="harga_beli_sebelum_pajak" type="text" class="form-control" name="harga_beli_sebelum_pajak" disabled value="{{$produk->harga_beli}}" readonly>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_jual" class="col-md-3 control-label">Harga Toko Insan</label>
    <div class="col-md-3">
      <input id="harga_jual" type="text" class="form-control" name="harga_jual" disabled value="{{$produk->harga_jual}}" readonly>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_grosir" class="col-md-3 control-label">Harga Grosir</label>
    <div class="col-md-3">
      <input id="harga_grosir" type="number" class="form-control" name="harga_grosir" id="harga_grosir"  value="{{ $produk->harga_grosir }} "required>
      <span class="help-block with-errors"></span>
    </div>

    
    <label for="harga_indo" class="col-md-1 control-label">Harga Indomart</label>
    <div class="col-md-3">
      <input type="number" class="form-control" id="harga_indo" name="harga_indo" value="{{$produk->harga_indo}}" required>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_alfa" class="col-md-3 control-label">Harga Alfamart</label>
    <div class="col-md-3">
      <input id="harga_alfa" type="number" value="{{$produk->harga_alfa}}" class="form-control" name="harga_alfa" required>
      <span class="help-block with-errors"></span>
    </div>

    
    <label for="harga_olshop" class="col-md-1 control-label">Harga Olshop</label>
    <div class="col-md-3">
      <input type="number" class="form-control" name="harga_olshop" id="harga_olshop" value="{{$produk->harga_olshop}}" >
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
