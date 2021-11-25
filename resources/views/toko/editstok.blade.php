<div class="modal" id="modal-form" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <form class="form-horizontal" id="form_action" action="{{route('stockToko.tambah')}}" method="post">
   {{ csrf_field() }} 

   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title"></h3>
   </div>
            
<div class="modal-body">
   
   <input type="hidden" id="id" name="id">
   <div class="form-group">
      <label for="nama" class="col-md-3 control-label">Kode Produk</label>
      <div class="col-md-6">
         <input id="kode" type="text" class="form-control" name="kode" readonly>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   
   <div class="form-group">
      <label for="pic" class="col-md-3 control-label">Nama Produk</label>
      <div class="col-md-6">
         <input id="nama" type="text" class="form-control" name="nama" readonly>
         <span class="help-block with-errors"></span>
      </div>
   </div>

   <div class="form-group">
      <label for="jumlah" class="col-md-3 control-label">Jumlah</label>
      <div class="col-md-6">
         <input id="jumlah" type="text" class="form-control" name="jumlah" required>
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
