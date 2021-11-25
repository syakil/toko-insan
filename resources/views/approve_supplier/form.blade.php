<div class="modal" id="modal-form" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <form class="form-horizontal" id="form_action" action="{{route('supplier.tambah')}}" method="post">
   {{ csrf_field() }} 

   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title"></h3>
   </div>
            
<div class="modal-body">
   
   <input type="hidden" id="id" name="id">
   <div class="form-group">
      <label for="nama" class="col-md-3 control-label">Nama Supplier</label>
      <div class="col-md-6">
         <input id="nama" type="text" class="form-control" name="nama" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   
   <div class="form-group">
      <label for="pic" class="col-md-3 control-label">PIC</label>
      <div class="col-md-6">
         <input id="pic" type="text" class="form-control" name="pic" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   <div class="form-group">
      <label for="alamat" class="col-md-3 control-label">Alamat</label>
      <div class="col-md-8">
         <input id="alamat" type="text" class="form-control" name="alamat" required>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   <div class="form-group">
      <label for="telpon" class="col-md-3 control-label">Telpon</label>
      <div class="col-md-6">
         <input id="telepon" type="text" class="form-control" name="telepon" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
   </div>
   
   <div class="form-group">
      <label for="norek" class="col-md-3 control-label">No Rekening</label>
      <div class="col-md-6">
         <input id="norek" type="text" class="form-control" name="norek" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   
   <div class="form-group">
      <label for="nama_rek" class="col-md-3 control-label">Nama Rekening</label>
      <div class="col-md-6">
         <input id="nama_rek" type="text" class="form-control" name="nama_rek" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   
   <div class="form-group">
      <label for="bank" class="col-md-3 control-label">Bank</label>
      <div class="col-md-6">
         <input id="bank" type="text" class="form-control" name="bank" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   
   
   <div class="form-group">
      <label for="metode" class="col-md-3 control-label">Metode Bayar</label>
      <div class="col-md-6">
         <input id="metode" type="text" class="form-control" name="metode" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   
</div>
   
   <div class="modal-footer">
      <button type="submit" class="btn btn-primary btn-save"><i class="fa fa-floppy-o"></i> Simpan </button>
      <button type="button" class="btn btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
   </div>
      
   </form>

         </div>
      </div>
   </div>
