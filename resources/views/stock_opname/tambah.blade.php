<!-- Modal -->
<div class="modal fade" id="modal-tambah" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Tambah Produk</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      <form class="form-tambah" method="post" >
      {{ csrf_field() }} 
        <div class="form-group">
          <label for="kode_produk_create">Kode Produk</label>
          <input type="text" class="form-control" id="kode_produk_create" name="kode_produk" readonly>
        </div>

        <div class="form-group">
          <label for="nama_produk_create">Nama Produk</label>
          <input type="text" class="form-control" id="nama_produk_create" name="nama_produk" readonly>
        </div>
        
        <div class="form-group">
          <label for="stok">Stok</label>
          <input type="number" class="form-control" id="stok" name="stok">
        </div>

        
        <div class="form-group">
          <label for="exp_date">Tanggal Kadaluarsa</label>
          <input type="date" class="form-control" id="exp_date" name="exp_date">
        </div>


      </div>
      <div class="modal-footer">
        <button type="button" onclick="addItem()" class="btn btn-primary">Simpan</button>
      </form> 
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>