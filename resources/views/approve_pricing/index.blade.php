@extends('layouts.app')

@section('title')
  Daftar Produk 
@endsection

@section('header')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
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
            <div class="box-body"> 
      <div class="box-body"> 
            <div class="box-body"> 
                <table class="table table-striped tabel-pricing">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Unit</th>
                            <th>No PO</th>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual Lama</th>
                            <th>Margin Lama</th>
                            <th>Harga Jual Baru</th>
                            <th>Margin Baru</th>
                            <th>Perubahan</th>
                            <th>Harga Grosir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                </div>
        </div>
    </div>
</div>

@endsection

@section('script')



<script language="JavaScript">
var table, save_method;
$(function(){
  table = $('.tabel-pricing').DataTable({
    "processing" : true,
    "serverside" : true,
    "scrollX" : true,
    "ajax" : {
      "url" : "{{ route('approve_pricing.data') }}",
      "type" : "GET"
    },
  }); 
});
</script>



@endsection
