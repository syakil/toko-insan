@extends('layouts.app')

@section('title')
  Tambah Permohonan Pembelian
@endsection

@section('breadcrumb')
   @parent
   <li>permohonan pembelian</li>
@endsection

@section('header')  
   <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />
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
  <div class="col-xs-6">
    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body">  
      <form action="{{route('permohonan_pembelian.store')}}" method="post">
         {{csrf_field()}}
         <div class="form-group">
            <label for="kode">Produk</label><br>
            <select class="form-control kode" id="kode"  name="kode" >
            @foreach($produk as $list)
               <option value="{{$list->kode_produk}}">{{$list->kode_produk}} - {{$list->nama_produk}}</option>
            @endforeach
            </select>
         </div>

         <div class="form-group">
            <label for="jumlah">Jumlah Permintaan</label>
            <input type="number" class="form-control" id="jumlah" name="jumlah" required>
         </div>
         
         <div class="form-group">
            <label for="harga">Harga Permintaan</label>
            <input type="number" class="form-control" id="harga" name="harga" required>
         </div>
         
         <div class="form-group">
            <label for="supplier">Supplier</label><br>
            <select class="form-control supplier" id="supplier"  name="supplier">
            @foreach($supplier as $list)
               <option value="{{$list->id_supplier}}">{{$list->nama}}</option>
            @endforeach
            </select>
         </div>

      </div>
      <div class="modal-footer">
        <a href="{{route('permohonan_pembelian.index')}}" type="button" class="btn btn-warning" data-dismiss="modal">Batal</a>
        <button type="submit" class="btn btn-primary">Proses</button>
      </form>     

      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.kode').select2({
      maximumSelectionLength: 3
    });
    $('.supplier').select2({
      maximumSelectionLength: 3
    });
});
</script>
@endsection