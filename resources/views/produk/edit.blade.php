@extends('layouts.app')

@section('title')
  Edit Harga Jual
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
        
      <form action="{{ route('produk.harga_jual',$produk->kode_produk)}}"class="form-horizontal"  data-toggle="validator" method="post">
    {{ csrf_field() }} {{ method_field('POST') }}
        


  <!-- <input type="hidden" id="id" name="id" value="{{$produk->kode_produk}}"> -->
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
      <input id="nama" type="text" class="form-control" name="nama" value="{{$produk->nama_produk}}"readonly>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="kategori" class="col-md-3 control-label">Kategori</label>
    <div class="col-md-6">
      <select id="kategori" type="text" class="form-control" name="kategori" required>
        <option value=""> -- Pilih Kategori-- </option>
        @foreach($kategori as $list)
        <option value="{{ $list->id_kategori }}">{{ $list->nama_kategori }}</option>
        @endforeach
      </select>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="merk" class="col-md-3 control-label">Merk</label>
    <div class="col-md-6">
      <input id="merk" type="text" class="form-control" name="merk" value="{{$produk->merk}}" required>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_beli" class="col-md-3 control-label">Harga Beli</label>
    <div class="col-md-3">
      <input id="harga_beli" type="text" class="form-control" name="harga_beli" value="{{$produk->harga_beli}}" readonly>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="diskon" class="col-md-3 control-label">Diskon</label>
    <div class="col-md-2">
      <input id="diskon" type="text" class="form-control" name="diskon" value="{{$produk->diskon}}" required>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_jual" class="col-md-3 control-label">Harga Jual Umum</label>
    <div class="col-md-3">
      <input id="harga_jual" type="text" class="form-control" name="harga_jual" value="{{$harga_jual}}" required>
      <span class="help-block with-errors"></span>
    </div>

    <label for="competitor1" class="col-md-1 control-label">Alfamart</label>
    <div class="col-md-3">
      <input  type="text" class="form-control" id="competitor1" onkeyup="hitung2();">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_jual_insan" class="col-md-3 control-label">Harga Jual Insan</label>
    <div class="col-md-3">
      <input id="harga_jual_insan" type="text" class="form-control" name="harga_jual_insan" value="{{$produk->harga_jual_insan}}" required>
      <span class="help-block with-errors"></span>
    </div>

    
    <label for="competitor2" class="col-md-1 control-label">Indomart</label>
    <div class="col-md-3">
      <input type="text" class="form-control" id="competitor2" onkeyup="hitung2();">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="harga_jual_pabrik" class="col-md-3 control-label">Harga Jual Pabrik</label>
    <div class="col-md-3">
      <input id="harga_jual_pabrik" type="text" class="form-control" name="harga_jual_pabrik" value="{{$produk->harga_jual_pabrik}}" required>
      <span class="help-block with-errors"></span>
    </div>

    
    <label for="competitor3" class="col-md-1 control-label">Carefour</label>
    <div class="col-md-3">
      <input type="text" class="form-control" id="competitor3" onkeyup="hitung2();">
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="stok" class="col-md-3 control-label">Stok</label>
    <div class="col-md-2">
      <input id="stok" type="text" class="form-control" name="stok" value="{{$produk->stok}}" readonly>
      <span class="help-block with-errors"></span>
    </div>

    <label for="avg" class="col-md-3 control-label">Avg</label>
    <div class="col-md-2">
      <input readonly id="avg" type="text" class="form-control" id="avg" required>
      <span class="help-block with-errors"></span>
    </div>
  </div>


   
   <div class="modal-footer">
      <button type="submit" class="btn btn-primary btn-save"><i class="fa fa-floppy-o"></i> Simpan </button>
      <a href="{{ route('produk.index') }}" class="btn btn-warning "><i class="fa fa-arrow-circle-left"></i> <span>Batal</span></a>
   </div>
    
   </form>

      
      </div>
    </div>
  </div>
</div>


@endsection

@section('script')
<script>
function hitung2() {
    var competitor1 = $("#competitor1").val();
    var competitor2 = $("#competitor2").val();
    var competitor3 = $("#competitor3").val();
    var avg = (parseInt(competitor1)+parseInt(competitor2)+parseInt(competitor3))/3;
    $("#avg").val(Math.round(avg));
}
</script>
@endsection