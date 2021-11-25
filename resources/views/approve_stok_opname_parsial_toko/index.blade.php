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
                <form action="{{ route('approve_stok_opname_parsial_toko.store') }}" method="post">
                {{ csrf_field() }}
                <table class="table table-striped tabel-so">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Toko</th>
                            <th>Barcode</th>
                            <th>Nama Produk</th>
                            <th>Stok Sistem</th>
                            <th>Stok Opname</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-danger pull-right approve">Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')



<script language="JavaScript">

$(function(){
    var url = "{{route('approve_stok_opname_parsial_toko.data')}}"
    table = $('.tabel-so').DataTable({
        "processing" : true,
        "paging" : true,
        "serverside" : true,
        "reload":true,
        "ajax" : {
            "url" : url,
            "type" : "GET"
        }
    });
})
</script>



@endsection
