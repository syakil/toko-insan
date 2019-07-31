<div class="modal" id="modal-form" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <form class="form-horizontal" data-toggle="validator" method="post">
   {{ csrf_field() }} {{ method_field('POST') }}
   
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title"></h3>
   </div>
            
<div class="modal-body">
   
   <input type="hidden" id="id" name="id">
   <div class="form-group">
      <label for="trns" class="col-md-3 control-label">Pilih Jenis Transaksi</label>
      <div class="col-md-8">
         <select id="trns"  class="form-control" name="trns" autofocus required>
                  <span class="help-block with-errors"></span>
                  <option value="1">Bank</option>
                   <option value="2">Lainnya</option>
                   
      </select>
      </div>
   </div>

   <div class="form-group">
    <label for="coa" class="col-md-3 control-label">Pilih Bank</label>
    <div class="col-md-6">
      <select id="coa" type="text" class="form-control" name="coa" required>
        <option value=""> -- Pilih Bank-- </option>
        @foreach($coa as $list)
        <option value="{{ $list->kode_rek }}">{{ $list->nama_rek }}</option>
        @endforeach
      </select>
      <span class="help-block with-errors"></span>
    </div>
  </div>

   <div class="form-group">
      <label for="ket" class="col-md-3 control-label">Ket Pengeluaran</label>
      <div class="col-md-8">
         <input id="ket" type="text" class="form-control" name="ket" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
   
         </div>

   <div class="form-group">
      <label for="nominal" class="col-md-3 control-label">Nominal</label>
      <div class="col-md-3">
         <input id="nominal" type="number" class="form-control" name="nominal" required>
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
