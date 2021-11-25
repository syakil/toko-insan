@extends('layouts.app')

@section('title')
  Daftar Kategori
@endsection

@section('breadcrumb')
   @parent
   <li>kategori</li>
@endsection

@section('content')     
<div class="row">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-header">
        <a onclick="addForm()" class="btn btn-success"><i class="fa fa-plus-circle"></i> Tambah</a>
      </div>
      <div class="box-body">  

<table class="table table-striped">
<thead>
   <tr>
      <th width="30">No</th>
      <th>Nama Kategori</th>
      <th>Paramater Kadaluarsa</th>
      <th width="100">Aksi</th>
   </tr>
</thead>
<tbody></tbody>
</table>

      </div>
    </div>
  </div>
</div>

@include('kategori.form')
@endsection

@section('script')
<script type="text/javascript">
var table, save_method;
$(function(){
   table = $('.table').DataTable({
     "processing" : true,
     "ajax" : {
       "url" : "{{ route('kategori.data') }}",
       "type" : "GET"
     }
   }); 
   
   $('#modal-form form').on('submit', function(e){
      if(!e.isDefaultPrevented()){
         var id = $('#id').val();
         if(save_method == "add"){ url = "{{ route('kategori.store') }}";
      }else{ url = "{{ route('kategori.update',':id') }}";
            url = url.replace(':id',id)}
         console.log(id);
         $.ajax({
           url : url,
           type : "POST",
           data : $('#modal-form form').serialize(),
           success : function(data){
             $('#modal-form').modal('hide');
             table.ajax.reload();
             alert("Data Berhasil Di Ubah !");
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
   $('#modal-form').modal('show');
   $('#modal-form form')[0].reset();            
   $('.modal-title').text('Tambah Kategori');
}

function editForm(id){
   save_method = "edit";
   console.log(save_method);
   $('#modal-form form')[0].reset();
   url = "{{route('kategori.edit',':id')}}"
   url = url.replace(':id',id)
   $.ajax({
     url : url,
     type : "GET",
     dataType : "JSON",
     success : function(data){
       $('#modal-form').modal('show');
       $('.modal-title').text('Edit Kategori');
       
       $('#id').val(data.id_kategori);
       $('#nama').val(data.nama_kategori);
       $('#expired').val(data.expired);
       
     },
     error : function(){
       alert("Tidak dapat menampilkan data!");
     }
   });
}

function deleteData(id){
   if(confirm("Apakah yakin data akan dihapus?")){
     $.ajax({
       url : "kategori/"+id,
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
</script>
@endsection