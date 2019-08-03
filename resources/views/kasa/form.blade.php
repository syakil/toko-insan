<div class="modal" id="modal-form" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
     
   <form class="form-horizontal" data-toggle="validator" method="post" name="postform">
   {{ csrf_field() }} {{ method_field('POST') }}
   
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title"></h3>
   </div>
            
<div class="modal-body">
   
   <input type="hidden" id="id" name="id">
   <div class="form-group">
      <label for="seratus_ribu" class="col-md-3 control-label">100 Ribu</label>
      <div class="col-xs-2">
         <input id="seratus_ribu" type="text" tabindex="1" class="form-control" name="seratus_ribu"  onkeyup="sr();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
     
      <label for="jml_seratus_ribu" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_seratus_ribu" type="text" class="form-control" name="jml_seratus_ribu"  value="0">
         <span class="help-block with-errors"></span>
      </div>
   
      <label for="limapuluh_ribu" class="col-md-3 control-label">50 Ribu</label>
      <div class="col-xs-2">
         <input id="limapuluh_ribu" type="text" tabindex="2" class="form-control" name="limapuluh_ribu" onkeyup="lima();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
         <label for="jml_limapuluh_ribu" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_limapuluh_ribu" type="text" class="form-control" name="jml_limapuluh_ribu" value="0">
         <span class="help-block with-errors"></span>
      </div>
         <label for="duapuluh" class="col-md-3 control-label">20 Ribu</label>
      <div class="col-xs-2">
         <input id="duapuluh" type="text" tabindex="3" class="form-control" name="duapuluh" onkeyup="dua();" required>
         <span class="help-block with-errors"></span>
      </div>
   
      <label for="jml_duapuluh" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_duapuluh" type="text" class="form-control" name="jml_duapuluh" value="0">
         <span class="help-block with-errors"></span>
      </div>
         <label for="sepuluh" class="col-md-3 control-label">10 Ribu</label>
      <div class="col-xs-2">
         <input id="sepuluh" type="text" tabindex="4" class="form-control" name="sepuluh" onkeyup="sepu();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
         <label for="jml_sepuluh" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_sepuluh" type="text" class="form-control" name="jml_sepuluh" value="0">
         <span class="help-block with-errors"></span>
      </div>
         <label for="limaribu" class="col-md-3 control-label">5 Ribu</label>
      <div class="col-xs-2">
         <input id="limaribu" type="text" tabindex="5" class="form-control" name="limaribu" onkeyup="lb();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
         <label for="jml_limaribu" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_limaribu" type="text" class="form-control" name="jml_limaribu" value="0">
         <span class="help-block with-errors"></span>
      </div>
         <label for="duaribu" class="col-md-3 control-label">2 Ribu</label>
      <div class="col-xs-2">
         <input id="duaribu" type="text" tabindex="5" class="form-control" name="duaribu" onkeyup="duri();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
         <label for="jml_duaribu" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_duaribu" type="text" class="form-control" name="jml_duaribu" value="0">
         <span class="help-block with-errors"></span>
      </div>
         <label for="seribu" class="col-md-3 control-label">1 Ribu</label>
      <div class="col-xs-2">
         <input id="seribu" type="text" tabindex="6" class="form-control" name="seribu" onkeyup="se_ribu();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
         <label for="jml_seribu" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_seribu" type="text" class="form-control" name="jml_seribu" value="0">
         <span class="help-block with-errors"></span>
      </div>
         <label for="limaratus" class="col-md-3 control-label">500 Rupiah</label>
      <div class="col-xs-2">
         <input id="limaratus" type="text" tabindex="7" class="form-control" name="limaratus" onkeyup="lmrst();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
         <label for="jml_limaratus" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_limaratus" type="text" class="form-control" name="jml_limaratus"  value="0">
         <span class="help-block with-errors"></span>
      </div>
         <label for="seratus" class="col-md-3 control-label">100 rupiah</label>
      <div class="col-xs-2">
         <input id="seratus" type="text" tabindex="8" class="form-control" name="seratus" onkeyup="srts();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
         <label for="jml_seratus" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_seratus" type="text" class="form-control" name="jml_seratus" value="0"> 
         <span class="help-block with-errors"></span>
      </div>
         <label for="lima_puluh" class="col-md-3 control-label">50 Rupiah</label>
      <div class="col-xs-2">
         <input id="lima_puluh" type="text" tabindex="9" class="form-control" name="lima_puluh" onkeyup="lmplh();" autofocus required>
         <span class="help-block with-errors"></span>
      </div>
         <label for="jml_lima_puluh" class="col-md-3 control-label">Jumlah</label>
      <div class="col-xs-2">
         <input id="jml_lima_puluh" type="text" class="form-control" name="jml_lima_puluh" value="0">
         <span class="help-block with-errors"></span>
      </div>
      <label for="jumlah" class="col-md-3 control-label">Total Cash</label>
      <div class="col-xs-2">
         <input id="jumlah" type="text" class="form-control" name="jumlah" value="0" onkeyup="total_jml();">
         <span class="help-block with-errors"></span>
      </div>

<label for="jumlah" class="col-md-3 control-label">Total Penjualan Cash</label>
      <div class="col-xs-2">
         <input id="pendapatan" type="text" class="form-control" name="pendapatan" value="{{$pendapatan->pendapatan}}">
         <span class="help-block with-errors"></span>
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
