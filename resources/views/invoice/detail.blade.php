@extends('layouts.app')

@section('header')
<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
<meta name="csrf-token" content="{{ csrf_token() }}" />
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

<link rel="stylesheet" href="{{asset('public/plugins/iCheck/all.css')}}">
@endsection

@section('title')
  Invoice Pembelian <small>{{$nopo->nama}}</small>
@endsection

@section('breadcrumb')
   @parent
   <li>Detail Invoice</li>
@endsection

@section('content')     


<!-- Main content -->
<div class="row">
    <div class="col-xs-12">
    <div class="box">
    <form class="form-pembelian" action="{{ route('invoice.simpan') }}" method="post">
    {{ csrf_field() }}
        <div class="box-header">
        <div class="pull-right">
         <h3>
          PO-{{$nopo->id_pembelian}}
         </h3>
        </div>
        <br>
        <div class="form-check col-md-3">
          <label class="form-check-label" for="exampleCheck1">Harga Invoice + PPN 10%</label><br>
          <input type="checkbox" class="form-check-input" name="ppn" value="ppn"><br>
          <label>Tipe Pembayaran</label><br>
          <select name="tipe_bayar" class="form-control">
            <option value="0" selected>Pilih Tipe Bayar</option>
            <option value="1">TOP</option>
            <option value="2">Cash</option>
          </select>
          <label for="no-invoice">Tanggal Pembayaran</label>
          <input type="date" class="form-control" id="tanggal" name="tanggal">
          <label for="no-invoice">No. Invoice</label>
          <input type="text" class="form-control" id="no-invoice" name="no_invoice" placeholder="Nomer Invoice/Faktur">
        </div>

        </div>
        <div class="box-body"> 
            <table class="table  table-striped tabel-detail">
                <input type="hidden" name="id" value="{{$id}}">
                <thead>
                    <tr>
                        <th width='1%'>No.</th>
                        <th width='1%'>Barcode</th>
                        <th>Nama Barang</th>
                        <th width='1%'>Jumlah</th>
                        <th>Harga Invoice</th>
                        <th>Spesial Diskon</th>
                        <th>Diskon Lainya</th>
                        <th>Regular Diskon</th>
                        <th>Total + PPn 10%</th>
                    </tr>
                </thead>

                <tbody>
                   
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<div class="row">

<div class="col-md-6">
    <div id="tampil-bayar" style="background: #669900; color: #fff; font-size: 50px; text-align: center; height: 70px"></div>
    <div id="tampil-terbilang" style="background: #3c8dbc; color: #fff; font-size: 15px; padding: 10px"></div>
<br>
    <div class="box ">
      
        <div class="box-body">
          <button type="submit" class="btn btn-primary pull-right simpan" disabled>Simpan</button>
          <a href="#" onclick="hitung({{$id}})" class="btn pull-right hitung">Hitung</a>
          </form>            
        </div>
            
    </div>

</div>

@include('invoice.diskon')
</div>


@include('invoice.produk')
@include('invoice.modal-diskon')
@endsection
    <!-- /.content -->
@section('script')

<script>
  var msg = '{{Session::get('alert')}}';
  var exist = '{{Session::has('alert')}}';
  if(exist){
    alert(msg);
  }
  
</script>

<script>
var table;
var spesial;
var diskon_lainya;
$(function(){
    $('.tabel-produk').DataTable();
    table = $('.tabel-detail').DataTable({
        "scrollY" : "300px",
        "paging" :false,
        "fixedHeader":true,
        "severside":true,
        "scrollX":"100%",
        "ajax":{
            url:"{{route('invoice.listDetail',$id)}}",
            type: "GET"
                }
        
        
    });
    spesial = $('.spesial-diskon').DataTable({
      "pageLength": 2,
        "severside":true,
        "searching":false,
        "scrollX": "100%",
        "ajax":{
            url:"{{route('invoice.listSpesial',$id)}}",
            type: "GET"
        }
    });
    
    diskon_lainya = $('.diskon-lainya').DataTable({
      "pageLength": 2,
        "severside":true,
        "searching":false,
        "scrollX": "100%",
        "ajax":{
            url:"{{route('invoice.listDiskonLainya',$id)}}",
            type: "GET"
        }
    });
});
</script>

<script>
function showProduct(){
  $('#modal-produk').modal('show');
}
</script>


<script>
function showDiskon(){
  $('#modal-diskon').modal('show');
}
</script>


<script>
function selectItem(kode){
    $('#modal-produk').modal('hide');
    $('.input-spesial-diskon').val(kode);
    addItem();
}

</script>


<script>

function addItem(){ 
  $.ajax({
    url : "{{ route('invoice.addSpesial')}}",
    type : "POST",
    data : $('.form-spesial-diskon').serialize(),
    success : function(data){
      table.ajax.reload();
      spesial.ajax.reload();
      $('.input-spesial-diskon').val();
                 
    },
    error : function(){
      alert("Tidak dapat menyimpan data!");
    }   
  });
}
</script>


<script>
function invoice(id){
  var url = '{{ route("invoice.updateinvoice", ":id") }}';
  url = url.replace(':id', id);
  $.ajax({
        url : url,
        type : "POST",
        data : $('.form-pembelian').serialize(),
        success : function(data){
          $('.simpan').attr('disabled',true);
        },
        error : function(){
          alert("Tidak dapat menyimpan data!");
          $('.simpan').attr('disabled',true);
        }   
     });
}
</script>



<script>
function perhitungan(){
  var url = '{{ route("invoice.perhitungan", $id) }}';
  $.ajax({
        url : url,
        type : "GET",
        success : function(data){
            alert("Data Berhasil di Proses");
            table.ajax.reload();
            $('.simpan').attr('disabled',true);
        },
        error : function(){
          alert("Tidak dapat menyimpan data!");
          $('.simpan').attr('disabled',true);
        }   
     });
}
</script>


<script>
function spesial_diskon(id){
  var url = '{{ route("invoice.updatespesial", ":id") }}';
  url = url.replace(':id', id);
  $.ajax({
        url : url,
        type : "POST",
        data : $('.form-pembelian').serialize(),
        success : function(data){
            alert("Data berhasil ubah");
            spesial.ajax.reload();  
            $('.simpan').attr('disabled',true);
        },
        error : function(){
          alert("Tidak dapat menyimpan data!");
          $('.simpan').attr('disabled',true);
        }   
     });
}
</script>

<script>
function hitung(id){
  var url = '{{ route("invoice.hitung", ":id") }}';
  url = url.replace(':id', id);
  $.ajax({
        url : url,
        type : "POST",
        data: $('.form-pembelian').serialize(),
        success : function(data){
            if (data.alert) {
              alert(data.alert);
            }else{
              alert(data.pesan);
              table.ajax.reload();
              $('#tampil-bayar').html("<small>Bayar:</small> Rp. "+data.bayarrp);
              $('#tampil-terbilang').text(data.terbilang);
              $('.simpan').attr('disabled',false);
            }
        },
        error : function(){
          alert("Tidak dapat menyimpan data!");
        }   
     });
} 
</script>



<script>
function spesial_diskon_2(id){
  var url = '{{ route("invoice.updatespesial_2", ":id") }}';
  url = url.replace(':id', id);
  $.ajax({
        url : url,
        type : "POST",
        data : $('.form-spesial').serialize(),
        success : function(data){
            alert("Data berhasil ubah");
            table.ajax.reload();
            spesial.ajax.reload();  
            $('.simpan').attr('disabled',true);
        },
        error : function(){
          alert("Tidak dapat menyimpan data!");
        }   
     });
}
</script>


<script>
function regular_diskon(id){
var url = '{{ route("invoice.updateregularppn", ":id") }}';
url = url.replace(':id', id);
$.ajax({
    url : url,
    type : "POST",
    data : $('.form-pembelian').serialize(),
    success : function(data){
        $('.simpan').attr('disabled',true);
    },
    error : function(){
      alert("Tidak dapat menyimpan data!");
      $('.simpan').attr('disabled',true);
    }   
  });
}
</script>



<script>
function deleteItem(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "../delete/spesial/"+id,
       type : "GET",
       success : function(data){
        table.ajax.reload(); 
        spesial.ajax.reload();
        $('.simpan').attr('disabled',true);
       },
       error : function(){
         alert("Tidak dapat menghapus data!");
         $('.simpan').attr('disabled',true);
       }
     });
   }
};
</script>


<script>
function deleteDiskon(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "../delete/diskon/"+id,
       type : "GET",
       success : function(data){
        table.ajax.reload(); 
        spesial.ajax.reload();
        diskon_lainya.ajax.reload();
        $('.simpan').attr('disabled',true);
       },
       error : function(){
         alert("Tidak dapat menghapus data!");
         $('.simpan').attr('disabled',true);
       }
     });
   }
};
</script>

@endsection