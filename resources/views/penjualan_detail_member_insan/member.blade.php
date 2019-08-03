
<div class="modal" id="modal-member" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
     

   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Cari Member Nurinsani</h3>
   </div>
            
<div class="modal-body">
   <table class="table table-striped tabel-member-awal">
      <thead>
         <tr>
            <th>Kode Member</th>
            <th>Nama Member</th>
            <th>Alamat</th>
            <th>Plafond</th>
            <th>Musawamah</th>            
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
            <th>{{ number_format($data->plafond) }}</th>
            <th>{{ number_format($data->os) }}</th>        
            <th>{{ $data->status_member }}</th>
            <th><a href="/toko/memberinsan/{{ $data->kode_member }}/baru" class="btn btn-primary"><i class="fa fa-check-circle"></i> Pilih</a></th>
                              
          </tr>
         @endforeach
      </tbody>
   </table>

</div>
      
         </div>
      </div>
   </div>
   @section('script')
   
   <script>
      $(document).ready( function () {
       $('.tabel-member-awal').DataTable();
   } );
   </script>
      @endsection