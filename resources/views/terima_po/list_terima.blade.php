<div class="modal" id="modal-list_terima" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Pilih Pembelian</h3>
   </div>
            
<div class="modal-body">
   <table class="table table-striped tabel-supplier">
      <thead>
         <tr>
            <th>Nomor PO</th>
            <th>Supplier</th>
            <th>Gudang</th>
            <th>Aksi</th>
         </tr>
      </thead>
      <tbody>
         @foreach($pembelian as $data)
         <tr>
            <th>{{ $data->id_pembelian }}</th>
            <th>{{ $data->nama }}</th>
            <th>{{ $data->kode_gudang }}</th>
            <th><a href="terima_po/{{ $data->id_pembelian }}/tambah" class="btn btn-primary"><i class="fa fa-check-circle"></i> Pilih</a></th>
          </tr>
         @endforeach
      </tbody>
   </table>

</div>
      
         </div>
      </div>
   </div>