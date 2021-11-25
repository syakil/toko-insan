
<div class="modal" id="modal-kelompok" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-md">
      <div class="modal-content">
     
     

   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Cari Kelompok Nurinsani</h3>
   </div>
            
<div class="modal-body">
   <table class="table table-striped tabel-member-awal">
      <thead>
         <tr>
            <th>Kode Kelompok</th>
            <th>Nama Kelompok</th>
            <th>Aksi</th>
         </tr>
      </thead>
      <tbody>
         @foreach($kelompok as $data)
         <tr>
            <td>{{ $data->code_kel }}</td>
            <td>{{$data->nama_kel}}</td>
            <td><a  onclick="selectKelompok()" data-id="{{ strval($data->code_kel) }}"data-nama="{{$data->nama_kel}}" class="btn btn-primary pilih-kelompok"><i class="fa fa-check-circle"></i> Pilih</a></td>
         </tr>
      @endforeach
      </tbody>
   </table>

</div>
      
         </div>
      </div>
   </div>