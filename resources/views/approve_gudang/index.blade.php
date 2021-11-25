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
    <div class="col-md-3">
        <div class="box box-default collapsed-box">
            <div class="box-header with-border">

                <label for="exampleFormControlSelect1">Pilih Toko</label>
                <select class="form-control" id="toko">
                    <option value="unit">pilih toko</option>
                    @foreach($unit as $id)
                    <option value="{{$id->kode_toko}}">{{$id->nama_toko}}</option>
                    @endforeach
                </select>
            </div>
        <!-- /.box-body -->
        </div>
    <!-- /.box -->
    </div>

    <div class="col-xs-12">
        <div class="box">
            <div class="box-body"> 
      <div class="box-body"> 
            <div class="box-body"> 
                <form action="{{ route('approve_gudang.store') }}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="unit" id="unit">
                <table class="table table-striped tabel-so">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Barcode</th>
                            <th>Nama Produk</th>
                            <th>Stok Opname</th>
                            <th>Stok Sistem</th>
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

$("select#toko").change(function(){        
    var unit = $(this).children("option:selected").val();
    $('.tabel-so').DataTable().destroy();
    getData(unit);
});


function getData(unit) {

    var url = "{{route('approve_gudang.data',':unit')}}"
    url = url.replace(':unit',unit)
    $("#unit").val(unit);
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
}
</script>



@endsection
