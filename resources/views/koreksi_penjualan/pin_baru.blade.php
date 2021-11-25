<div class="modal fade bd-example-modal-md" id="modal-pin-baru"tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
    
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PIN Baru</h5>
      </div>

      <div class="modal-body">

      <form id="pin-baru-form" action="" method="post">      
      {{csrf_field()}}

      <input type="hidden" class="form-control" name="id_member_baru" id="id_member_baru" readonly>
        
      <div class="form-group">
    
        <label for="id">Kode Transaski</label>
        <input type="text" class="form-control" name="id_penjualan_baru" id="id_penjualan_baru" readonly>

      </div>

      <div class="form-group">
        
        <label for="id">ID Member</label>
        <input type="text" class="form-control" name="id_label_baru" id="id_label_baru" readonly>

      </div>

        <div class="form-group">
          <label for="input-pin">Masukan KTP</label>
          <input type="text" class="form-control" id="nik" name="nik" placeholder="Masukan KTP" required>
        </div>

        <div class="form-group">
          <label for="input-pin">Masukan PIN Baru</label>
          <input type="password" class="form-control allownumericwithoutdecimal" id="pin-baru" maxlength="6" name="pin_baru" minlength="6" placeholder="Masukan PIN" required>
          <small class="form-text text-muted">max 6 Angka</small>
        </div>

        <div class="form-group">
          <label for="input-pin">Konfirmasi PIN</label>
          <input type="password" class="form-control allownumericwithoutdecimal" id="pin-konf" maxlength="6" name="pin_konf" minlength="6" placeholder="Masukan PIN" required>
        </div>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Keluar</button>
        <button type="submit" class="btn btn-primary">Proses</button>
      </form >
      </div>
    </div>
  </div>
</div>
