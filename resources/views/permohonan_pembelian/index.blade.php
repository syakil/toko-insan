@extends('layouts.app')

@section('title')
  Daftar Permohonan Pembelian
@endsection

@section('breadcrumb')
   @parent
   <li>permohonan pembelian</li>
@endsection

@section('header')

  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
  
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
      <a href="{{route('permohonan_pembelian.tambah')}}" class="btn btn-primary">
        Tambah Permintaan
      </a>

      </div>
      <div class="box-body">  
        <table class="table table-bordered tabel-permohonan">
          <thead>
            <tr>
              <th>Tanggal Pembuatan</th>
              <th>Kode Permohonan</th>
              <th>Supplier</th>
              <th>Kode Produk</th>
              <th>Nama Produk</th>
              <th>Jumlah</th>
              <th>Harga Satuan</th>
              <th>Total Harga</th>
              <th>Keterangan</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('permohonan_pembelian.edit')

@endsection

@section('script')


<script type="text/javascript">
var table, save_method, table1;
$(function(){
    table = $('.tabel-permohonan').DataTable({
      "processing" : true,
      "serverside" : true,
      dom: 'Bfrtip',
      buttons: [
          'excel'
      ],
      "ajax" : {
        "url" : "{{ route('permohonan_pembelian.data') }}",
        "type" : "GET"
      }
    }); 
    table1 = $('.tabel-detail').DataTable({     
    });
});

function addForm(){
   $('#modal-supplier').modal('show');        
}

function showDetail(id){

  url = "{{route('permohonan_pembelian.edit',':id')}}";
  url = url.replace(':id',id);
  $.ajax({
    url : url,
    type : "GET",
    dataType : "JSON",
    success : function(data){
      $('#modal-edit').modal('show');
      $('.modal-title').text('Edit Permohonan');
      $('#kode').val(data.kode_produk);
      $('#id').val(data.id_permohonan);
      $('#nama').val(data.nama_produk);
      $('#supplier').val(data.nama);
      $('#jumlah').val(data.jumlah);
      $('#harga').val(data.harga_beli);     
    },
    error : function(){
      alert("Tidak dapat menampilkan data!");
    }
  });
}

function deleteData(id){
  if(confirm("Apakah yakin data akan dihapus?")){
    
    url = "{{route('permohonan_pembelian.delete',':id')}}";
    url = url.replace(':id',id);

    $.ajax({
      url : url,
      type : "get",
      data : {'_method' : 'DELETE', '_token' : $('meta[name=csrf-token]').attr('content')},
      success : function(data){        
        swal("Selamat !", 'Permohonan Berhasil Di Hapus!', "success"); 
        table.ajax.reload();
      },
      error : function(){
        swal("Maaf !", 'Data Tidak Ditemukan!', "error"); 
      }
    });
  }
}
</script>

@endsection
