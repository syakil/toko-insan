<div class="modal" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
 
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Koreksi Detail Pembelian</h3>
   </div>
            
      <div class="modal-body">
      <form action="{{route('permohonan_pembelian.update')}}" method="post">
         {{csrf_field()}}
         <input type="hidden" name="id" id="id">
         <div class="form-group">
            <label for="kode">Produk</label><br>
            <input class="form-control kode" id="kode" name="kode" readonly >
         </div>

         <div class="form-group">
            <label for="nama">Nama Produk</label><br>
            <input class="form-control" id="nama" name="nama" readonly>
         </div>

         <div class="form-group">
            <label for="jumlah">Jumlah Permintaan</label>
            <input type="number" class="form-control" id="jumlah" name="jumlah" required>
         </div>
         
         <div class="form-group">
            <label for="harga">Harga Permintaan</label>
            <input type="number" class="form-control" id="harga" name="harga" required>
         </div>
         
         <div class="form-group">
            <label for="supplier">Supplier</label><br>
            <input class="form-control" id="supplier"  name="supplier" readonly>
         </div>

      </div>
      <div class="modal-footer">
        <a href="{{route('permohonan_pembelian.index')}}" type="button" class="btn btn-warning" data-dismiss="modal">Batal</a>
        <button type="submit" class="btn btn-primary">Proses</button>
      </form>     


        </div>
    </div>
</div>
