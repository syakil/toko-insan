@extends('layouts.app')

@section('title')
  Selisih Kirim Barang
@endsection

@section('header')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
@endsection


@section('breadcrumb')
   @parent
   <li>selisih_kirim_barang</li>
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-body"> 
                <table class="table table-striped tabel-selisih">
                    <thead>
                        <tr>
                            <th width='1%'>No.</th>
                            <th>Tanggal Transaksi</th>
                            <th>Surat Jalan</th>
                            <th>Unit</th>
                            <th>Barcode</th>
                            <th>Nama Produk</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- /.content -->
@endsection

@section('script')


<script>

var table;
    $(function(){
   table = $('.tabel-selisih').DataTable({
     "processing" : true,
     "serverside" : true,
     "ajax" : {
       "url" : "{{ route('selisih_kirim_barang.data') }}",
       "type" : "GET"
     }
   });
});


</script>



@endsection