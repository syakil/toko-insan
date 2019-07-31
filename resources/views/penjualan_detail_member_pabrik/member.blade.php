<div class="modal" id="modal-pabrik" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Cari Member Pabrik</h3>
   </div>
            
<div class="modal-body">
   <table class="table table-striped tabel-produk">
      <thead>
         <tr>
            <th>Kode Member</th>
            <th>Member</th>
            <th>Unit</th>
            <th>Plafond</th>
            <th>Status</th>
            <th>Aksi</th>
         </tr>
      </thead>
      <tbody>
      @foreach($member as $data)
         <tr>
            <th>{{ $data->kode_member }}</th>
            <th>{{ $data->nama }}</th>
            <th>{{ $data->unit }}</th>
            <th>{{ $data->plafond }}</th>
            <th>{{ $data->status }}</th>
            <th><a href="/toko/memberpabrik/{{ $data->kode_member }}/baru" class="btn btn-primary"><i class="fa fa-check-circle"></i> Pilih</a></th>
                              
          </tr>
         @endforeach
      </tbody>
   </table>

</div>
      
         </div>
      </div>
   </div>
