<div class="modal" id="modal-pabrik" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Cari Member Pabrik</h3>
   </div>
            
<div class="modal-body">
   <table class="table table-striped tabel-member-awal">
      <thead>
         <tr>
            <th>Kode Member</th>
            <th>Member</th>
            <th>Unit</th>
            <th>Plafond</th>
            <th>Musawamah</th>
            <th>Status</th>
            <th>Aksi</th>
         </tr>
      </thead>
      <tbody>
      @foreach($member as $data)
         <tr>
            <th>{{ $data->id_member }}</th>
            <th>{{ $data->nama }}</th>
            <th>{{ $data->unit }}</th>
            <th>{{ $data->Plafond }}</th>
            <th>{{ $data->os }}</th>
            <th>{{ $data->status_member }}</th>
            <th><button onclick="getMemberPabrik({{ $data->id_member }})" class="btn btn-primary"><i class="fa fa-check-circle"></i> Pilih</button></th>
                              
          </tr>
         @endforeach
      </tbody>
   </table>

</div>
      
         </div>
      </div>
   </div>


