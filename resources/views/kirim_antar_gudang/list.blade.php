<div class="modal" id="list-hold" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Pilih Toko</h3>
   </div>
            
<div class="modal-body">
   <table class="table table-striped list-hold">
      <thead>     
         <tr>
            <th width="30">No</th>
            <th>No Surat Jalan</th>
            <th>Tanggal</th>
            <th>Toko</th>
            <th>Total Item</th>
            <th>Total Harga</th>
            <th width="100">Aksi</th>
         </tr>
      </thead>
      <tbody>
         @foreach($kirim as $data)
         <tr>
            <td>{{ $no++}}</td>
            <td>{{ $data->id_pembelian }}</td>
            <td>{{ tanggal_indonesia(substr($data->created_at, 0, 10), false) }}</td>
            <td>{{$data->nama_toko}}</td>
            <td>{{ number_format($data->total_item)}}</td>
            <td>Rp. {{format_uang($data->total_harga)}}</td>
            <td><a href="{{route('kirim_antar_gudang_detail.continued', $data->id_pembelian)}}" class="btn btn-primary"><i class="fa fa-check-circle"></i> Pilih</a></td>
          </tr>
         @endforeach
      </tbody>
   </table>

</div>
      
         </div>
      </div>
   </div>
