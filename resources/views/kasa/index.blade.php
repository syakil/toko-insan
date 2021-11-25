@extends('layouts.app')

@section('title')
  Daftar Cash Count
@endsection

@section('breadcrumb')
   @parent
   <li>Kasir</li>
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
        <a onclick="addForm()" class="btn btn-success"><i class="fa fa-plus-circle"></i> Tambah</a>
        <a onclick="printCard()" class="btn btn-info"><i class="fa fa-credit-card"></i> Cetak</a>
      </div>
      <div class="box-body"> 

<form method="post" id="form-member">
{{ csrf_field() }}
<table class="table table-striped">
<thead>
   <tr>
      <th width="20"><input type="checkbox" value="1" id="select-all"></th>
      <th width="20">No</th>
      <th>tanggal</th>
      <th>Kasir</th>
      <th>100 Ribu</th>
      <th>50 Ribu</th>
      <th>20 Ribu</th>
      <th>10 Ribu</th>
      <th>5 Ribu</th>
      <th>2 Ribu</th>
      <th>1 Ribu</th>
      <th>100</th>
      <th>Jumlah Cash</th>
      <th>Penjualan Cash</th>
      <th>Penjualan Musawamah</th>
      <th>Total</th>
      <th>Selisih</th>
   </tr>
</thead>
<tbody></tbody>
</table>
</form>

      </div>
    </div>
  </div>
</div>

@include('kasa.form')
@endsection

@section('script')
<script type="text/javascript">
var table, save_method;
$(function(){
   table = $('.table').DataTable({
     "processing" : true,
"scrollX" : "100%",
     "ajax" : {
       "url" : "{{ route('kasa.data') }}",
       "type" : "GET"
     },
     'columnDefs': [{
         'targets': 0,
         'searchable': false,
         'orderable': false
      }],
      'order': [1, 'asc']
   }); 

   $('#select-all').click(function(){
      $('input[type="checkbox"]').prop('checked', this.checked);
   });
   
   $('#modal-form form').validator().on('submit', function(e){
      if(!e.isDefaultPrevented()){
         var id = $('#id').val();
         if(save_method == "add") url = "{{ route('kasa.store') }}";
         else url = "kasa/"+id;
         
         $.ajax({
           url : url,
           type : "POST",
           data : $('#modal-form form').serialize(),
           dataType: 'JSON',
           success : function(data){
            if(data.msg=="error"){
              alert('Kode member sudah digunakan!');
              $('#kode').focus().select();
            }else{
              alert('Data sudah di simpan');
              $('#modal-form').modal('hide');
              table.ajax.reload();
            }
           },
           error : function(){
             alert("Tidak dapat menyimpan data!");
           }   
         });
         return false;
     }
   });
});

function addForm(){
   save_method = "add";
   $('input[name=_method]').val('POST');
   $('#modal-form').modal('show');
   $('#modal-form form')[0].reset();            
   $('.modal-title').text('Tambah cash');
   $('#kode').attr('readonly', false);
}

function editForm(id){
   save_method = "edit";
   $('input[name=_method]').val('PATCH');
   $('#modal-form form')[0].reset();
   $.ajax({
     url : "kasa/"+id+"/edit",
     type : "GET",
     dataType : "JSON",
     success : function(data){
       $('#modal-form').modal('show');
       $('.modal-title').text('Edit cash');
       
       $('#id').val(data.id_member);
       $('#kode').val(data.id).attr('readonly', true);
       $('#nama').val(data.nama);
       $('#alamat').val(data.alamat);
       $('#telpon').val(data.telpon);
       
     },
     error : function(){
       alert("Tidak dapat menampilkan data!");
     }
   });
}

function deleteData(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "kasa/"+id,
       type : "POST",
       data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
       success : function(data){
         table.ajax.reload();
       },
       error : function(){
         alert("Tidak dapat menghapus data!");
       }
     });
   }
}

function printCard(){
  if($('input:checked').length < 1){
    alert('Pilih data yang akan dicetak!');
  }else{
    $('#form-member').attr('target', '_blank').attr('action', "kasa/printeod").submit();
  }
}
</script>

<script>
function sr() {
  var seratus_ribu = document.getElementById('seratus_ribu').value;
      var kali1 = 100000;
      var hasil_kali1 = parseInt(seratus_ribu) * parseInt(kali1);
      if (!isNaN(hasil_kali1)) {
         document.getElementById('jml_seratus_ribu').value = hasil_kali1;
      } 
}
function lima() {
  var limapuluh_ribu = document.getElementById('limapuluh_ribu').value;
      var kali2 =50000;
      var hasil_kali2 = parseInt(limapuluh_ribu) * parseInt(kali2);
      if (!isNaN(hasil_kali2)) {
         document.getElementById('jml_limapuluh_ribu').value = hasil_kali2;
      } 
}

function dua() {
  var duapuluh = document.getElementById('duapuluh').value;
      var kali3 =20000;
      var hasil_kali3 = parseInt(duapuluh) * parseInt(kali3);
      if (!isNaN(hasil_kali3)) {
         document.getElementById('jml_duapuluh').value = hasil_kali3;
      } 
}
function sepu() {
  var sepuluh = document.getElementById('sepuluh').value;
      var kali4 =10000;
      var hasil_kali4 = parseInt(sepuluh) * parseInt(kali4);
      if (!isNaN(hasil_kali4)) {
         document.getElementById('jml_sepuluh').value = hasil_kali4;
      } 
      
}

function lb() {
  var limaribu = document.getElementById('limaribu').value;
      var kali5 =5000;
      var hasil_kali5 = parseInt(limaribu) * parseInt(kali5);
      if (!isNaN(hasil_kali5)) {
         document.getElementById('jml_limaribu').value = hasil_kali5;
      } 
 
}

function duri() {
  var duaribu = document.getElementById('duaribu').value;
      var kali6 =2000;
      var hasil_kali6 = parseInt(duaribu) * parseInt(kali6);
      if (!isNaN(hasil_kali6)) {
         document.getElementById('jml_duaribu').value = hasil_kali6;
      } 
      
}

function se_ribu() {
  var seribu = document.getElementById('seribu').value;
      var kali7 =1000;
      var hasil_kali7 = parseInt(seribu) * parseInt(kali7);
      if (!isNaN(hasil_kali7)) {
         document.getElementById('jml_seribu').value = hasil_kali7;
      } 
           
}

function lmrst() {
  var limaratus = document.getElementById('limaratus').value;
      var kali8 =500;
      var hasil_kali8 = parseInt(limaratus) * parseInt(kali8);
      if (!isNaN(hasil_kali8)) {
         document.getElementById('jml_limaratus').value = hasil_kali8;
      } 
           
}

function srts() {
  var seratus = document.getElementById('seratus').value;
      var kali9 =100;
      var hasil_kali9 = parseInt(seratus) * parseInt(kali9);
      if (!isNaN(hasil_kali9)) {
         document.getElementById('jml_seratus').value = hasil_kali9;
      } 
           
}
function lmplh() {
  var lima_puluh = document.getElementById('lima_puluh').value;
      var kali10 =50;
      var hasil_kali10 = parseInt(lima_puluh) * parseInt(kali10);
      if (!isNaN(hasil_kali10)) {
         document.getElementById('jml_lima_puluh').value = hasil_kali10;
      } 
           
}

function total_jml() {
  
         var a = document.getElementById('jml_seratus_ribu').value;
         var b = document.getElementById('jml_limapuluh_ribu').value;
         var c = document.getElementById('jml_duapuluh').value;
         var d = document.getElementById('jml_sepuluh').value;
         var e = document.getElementById('jml_limaribu').value;
         var f = document.getElementById('jml_duaribu').value;
         var g = document.getElementById('jml_seribu').value;
         var h = document.getElementById('jml_limaratus').value;
         var i = document.getElementById('jml_seratus').value;
         var j = 0;
         var tot = parseInt(a) + parseInt(b) + parseInt(c) + parseInt(d) + parseInt(e) + parseInt(f) + parseInt(g) + parseInt(h) + parseInt(i)+ parseInt(j);
      if (!isNaN(tot)) {
         document.getElementById('jumlah').value = tot;
      } 
           
}
</script>
@endsection

