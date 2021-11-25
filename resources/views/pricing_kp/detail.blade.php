@extends('layouts.app')

@section('header')

<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>

<style>
/* .dataTables_scrollHeadInner{
  width:100% !important;
}
.dataTables_scrollHeadInner table{
  width:100% !important;
} */


input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    /* display: none; <- Crashes Chrome on hover */
    -webkit-appearance: none;
    margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
}

input[type=number] {
    -moz-appearance:textfield;
    font-weight:bold;
     /* Firefox */
}

</style>

@endsection

@section('title')
  Barang di Terima
@endsection

@section('breadcrumb')
   @parent
   <li>Barang diterima</li>
@endsection

@section('content')     


<!-- Main content -->
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
      </div>
      <div class="box-body">  
      <form action="{{ route('pricing_kp.simpan',$id_pembelian)}}" class="form-pricing" method="post">
      {{ csrf_field() }}
        <table class="table table-striped" id="tabel-pricing">
            <thead>
              <tr>
                <th>No.</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>HPP Baru</th>
                <th>Harga Jual Terakhir</th>
                <th>Margin</th>
                <th>Harga Pasar</th>
                <th>Harga Jual NI</th>
                <th>Harga Jual Pabrik</th>
              </tr>
            </thead>

            <tbody>
                
            </tbody>
        </table>
          <button type="submit" class="btn btn-danger pull-right"><i class="fa fa-legal"></i> Proses</button>
        </form>
      </div>
    </div>
  </div>
</div>

    <!-- /.content -->
@endsection

@section('script')

<script>
var table;
table = $('#tabel-pricing').DataTable({
    "serverside" : true,
    "dom" : 'Bfrtip',
    "scrollX" : "100%",
    "ajax" :{
        url : "{{ route('pricing_kp.data_detail',$id_pembelian) }}",   
        type : "GET"
    }
});
</script>

<script>
function invoice(id) {
  var sum = 0;
  var url = '{{ route("pricing_kp.update_invoice", ":id") }}';
  url = url.replace(':id', id);
  $.ajax({
    url : url,
    type : "POST",
    data : $('.form-pricing').serialize(),
    success : function(data){
        alert('Data Invoice Berhasil di Ubah');
        table.ajax.reload();
    },
    error : function(){
        alert('Data Invoice Gagal di Ubah');
    }   
  });
}
</script>

<script>
function harga_jual(id) {
  var url = '{{ route("pricing_kp.harga_jual", ":id") }}';
  url = url.replace(':id', id);
  $.ajax({
    url : url,
    type : "POST",
    data : $('.form-pricing').serialize(),
    success : function(data){
        alert('Harga Jual Berhasil di Ubah');
        table.ajax.reload();
    },
    error : function(){
        alert('Harga Jual Berhasil Gagal Ubah');
    }   
  });
}
</script>

<script>
function harga_jual_ni(id) {
  var url = '{{ route("pricing_kp.harga_ni", ":id") }}';
  url = url.replace(':id', id);
  $.ajax({
    url : url,
    type : "POST",
    data : $('.form-pricing').serialize(),
    success : function(data){
        alert('Harga Jual Berhasil di Ubah');
        table.ajax.reload();
    },
    error : function(){
        alert('Harga Jual Berhasil Gagal Ubah');
    }   
  });
}
</script>

@endsection
