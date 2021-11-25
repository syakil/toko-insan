
<div class="modal" id="modal-diskon" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog">
      <div class="modal-content">
     
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title">Diskon Lainya</h3>
   </div>
            

<div class="modal-body form-horizontal">
<form action="../add/diskon-lainya" method="post">
{!! csrf_field() !!}
      <input type="hidden" name="id" value="{{$id}}">
      <div class="box-body">
         <div class="form-group">
         <label for="keterangan" class="col-sm-2 control-label">Keterangan</label>

         <div class="col-sm-10">
            <input type="text" class="form-control" name="keterangan" id="keterangan" placeholder="Keterangan Diskon" required>
         </div>
         </div>
         <div class="form-group">
         <label for="nominal" class="col-sm-2 control-label">Nominal</label>

         <div class="col-sm-10">
            <input type="text" class="form-control" name="nominal" id="nominal" placeholder="nominal" required>
         </div>
         </div>
      </div>
      <!-- /.box-body -->
      <div class="box-footer">
         <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
         <button type="submit" class="btn btn-primary pull-right">Tambah</button>
</form>
      </div>
</div>