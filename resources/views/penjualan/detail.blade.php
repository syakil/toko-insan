@extends('layouts.app')

@section('title')
  Daftar Detail Penjualan
@endsection

@section('header')

<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
<link href="//cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css" rel="stylesheet"/>
@endsection



@section('breadcrumb')
   @parent
   <li>penjualan</li>
@endsection

@section('header')

@endsection

@section('content')
<div class="row">
  <div class="col-md-9">
    <!-- DIRECT CHAT -->
    <div class="box box-warning">
      <div class="box-body">
      <div class="col-md-3">
        Tanggal Awal
        <input class="form-control col-sm-1" id="awal" type="date" value="{{date('Y-m-d')}}" id="example-date-input">
      </div>
      <div class="col-md-3">
        Tanggal Akhir
        <input class="form-control col-sm-1" id="akhir" type="date" value="{{date('Y-m-d')}}" id="example-date-input">
      </div>
      <br>
      <div class="col-md-3">
      <button type="button" class="btn btn-primary tanggal"><i class="fa fa-search"></i> Cari</button>
      <button type="button" class="btn btn-danger hapus" disabled><i class="fa fa-refresh"></i> Reset</button>  
      </div>
        
      </div>
    </div>
</div>

<div class="col-xs-12">
  <div class="box">
    <div class="box-body">  

<table class="table table-striped tabel-penjualan">
<thead>
   <tr>
      <th>Tanggal</th>
      <th>Unit</th>
      <th>Kode Transaksi</th>
      <th>Kode Member</th>
      <th>Nama Member</th>
      <th>Kode Produk</th>
      <th>Nama Produk</th>ds
      <th>Harga Jual + Tenor*</th>
      <th>Harga Normal</th>
      <th>Harga Beli</th>
      <th>Qty</th>
      <th>Sub Total Harga Jual</th>
      <th>Sub Total persediaan</th>
      <th>Sub Total Harga Beli</th>
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
<script type="text/javascript">
var table, save_method, table1;
function penjualan(awal,akhir){
  var url = "{{route('penjualan.data_detail',[':awal',':akhir'])}}";
  url = url.replace(':awal', awal);
  url = url.replace(':akhir', akhir);
   table = $('.tabel-penjualan').DataTable({
      "processing" : true,
      "serverside" : true,
      "scrollX":"100%",
      "dom": 'Bfrtip',
        buttons: [
            'excel'
        ],
      "ajax" : {
      "url" : url ,
      "type" : "GET"
     }
   }); 
   
};

function addForm(){
   $('#modal-supplier').modal('show');        
}

function showDetail(id){
    $('#modal-detail').modal('show');
}
</script>

<script>
$('.tanggal').on('click',function(){
  awal = $('#awal').val();
  akhir = $('#akhir').val();
  $('.tanggal').attr('disabled',true);
  $('.hapus').attr('disabled',false);
  penjualan(awal,akhir);
})
</script>


<script>
$('.hapus').on('click',function(){
  table.clear();
  table.destroy();
  $('.tanggal').attr('disabled',false);
  $('.hapus').attr('disabled',true);
})
</script>

<script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script> 

@endsection

