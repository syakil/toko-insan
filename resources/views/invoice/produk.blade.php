<div class="modal" id="modal-produk" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Cari Produk</h3>
   </div>
            
<div class="modal-body">
   <table class="table table-striped tabel-produk">
      <thead>
         <tr>
            <th>Kode Produk</th>
            <th>Nama Produk</th>
            <th>Aksi</th>
         </tr>
      </thead>
      <tbody>
         @foreach($produk as $data)
         <tr>
            <td>{{ $data->kode_produk }}</td>
            <td>{{ $data->nama_produk }}</td>
            <td><a onclick="selectItem({{ "$data->kode_produk" }})" data-kode="{{ $data->kode_produk }}" class="btn btn-primary kode"><i class="fa fa-check-circle"></i> Pilih</a></th>
          </tr>
         @endforeach
      </tbody>
   </table>

</div>
      
         </div>
      </div>
</div>