@extends('layouts.app')

@section('title')
  Daftar Penjualan
@endsection

@section('breadcrumb')
   @parent
   <li>penjualan</li>
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
        <table class="table table-striped tabel-penjualan">
          <thead>
            <tr>
                <th width="30">No</th>
                <th>Tanggal</th>
                <th>Kode Transaksi</th>
                <th>ID Member</th>
                <th>Nama</th>
                <th>Total Item</th>
                <th>Total Penjualan</th>
                <th width="100">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('koreksi_penjualan.pin')
@include('koreksi_penjualan.pin_baru')

@endsection

@section('script')
<script type="text/javascript">
var table, save_method, table1;
$(function(){
  table = $('.tabel-penjualan').DataTable({
    "processing" : true,
    "serverside" : true,
    "responsive" : true,
    "ajax" : {
      "url" : "{{ route('koreksi_penjualan.data') }}",
      "type" : "GET"
    }
  }); 
});
</script>
<script>
function getMember(kode){
  url = "{{route('koreksi_penjualan.check',':id')}}";
  url = url.replace(':id',kode);
  $.ajax({
    url: url,
    data:{id : kode},
    method: 'get',
    dataType: 'json',
    success: function(data){
      console.log(data);
      var pin = btoa(data.PIN);
      var nama = data.nama;
      $('#pin').val(data.PIN);
      $('#id_member').val(data.kode_member);
      $('#id_penjualan').val(data.id_penjualan);
      $('#id_penjualan_baru').val(data.id_penjualan);
      $('#id_label').val(data.kode_member + ' - ' + data.nama);
      $('#id_member_pin_baru').val(data.kode_member);
      $('#id_label_baru').val(data.kode_member + ' - ' + nama);
      $('#id_member_baru').val(data.kode_member);

      if (atob(pin) == 0) {
          $('#modal-pin-baru').modal('show'); 
          $('#pin-baru-form').attr('action',"{{route('koreksi_penjualan.new_pin')}}")
      }else{
          $('#modal-pin').modal('show'); 
          $('#pin-form').attr('action',"{{route('koreksi_penjualan.newSessionCredit')}}")
      }
      
      $('#lupa_pin').click(function(){
          $('#modal-pin').modal('hide');
          $('#modal-pin-baru').modal('show');
      })
    },
    error: function(){
        alert("Member Tidak Ada");
    }
  });
}  
</script>

@endsection