<!-- Modal -->
<div class="modal fade" id="so-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">List Produk</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
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
                <th>{{ $data->kode_produk }}</th>
                <th>{{ $data->nama_produk }}</th>
                <th><a onclick="selectItem({{ $data->kode_produk }})" data-id="{{ $data->kode_produk }}" class="btn btn-primary kode"><i class="fa fa-check-circle"></i> Pilih</a></th>
            </tr>
            @endforeach
        </tbody>
        </table>
      </div>
    </div>
  </div>
</div>