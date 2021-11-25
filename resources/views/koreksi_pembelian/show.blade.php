<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Ganti Barcode</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      <form action="{{route('koreksi_pembelian.update')}}" method="post" >
      {{csrf_field()}}
        <div class="form-group">
        <input type="hidden" name="id" id="id">
          <label for="kode_baru">Barcode Baru</label>
          <input type="number" class="form-control" id="kode_baru" name="kode_baru" required>
        </div>
        <div class="form-group">
          <label for="kode_baru">Qty</label>
          <input type="number" class="form-control" name="qty" id="qty" required>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </form>
      </div>
    </div>
  </div>
</div>