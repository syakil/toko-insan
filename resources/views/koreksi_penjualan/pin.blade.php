<div class="modal fade bd-example-modal-sm" id="modal-pin"tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
    
    <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">PIN</h5>
      </div>

      <div class="modal-body">

        <form action="" id="pin-form" method="post">
          {{csrf_field()}}

          <input type="hidden" class="form-control" name="pin" id="pin" readonly>
          <input type="hidden" class="form-control" name="id_member" id="id_member" readonly>
        
        <div class="form-group">
      
          <label for="id">Kode Transaski</label>
          <input type="text" class="form-control" name="id_penjualan" id="id_penjualan" readonly>

        </div>

        <div class="form-group">
        
          <label for="id">ID Member</label>
          <input type="text" class="form-control" name="id_label" id="id_label" readonly>

        </div>

        <div class="form-group">
        
          <label for="input-pin">Masukan PIN</label>
          <input type="password" class="form-control allownumericwithoutdecimal" name="pin" maxlength="6" minlength="6" id="input-pin" placeholder="Masukan PIN" pattern="[0-9]*" inputmode="numeric" required>
          <a href="#" id="lupa_pin"><small class="form-text text-muted">Lupa PIN ?</small></a>

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