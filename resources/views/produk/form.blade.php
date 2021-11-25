<div class="modal" id="modal-form" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
    
   <form class="form-horizontal" id="form_produk" action="{{route('produk.store')}}" method="post">
   {{ csrf_field() }} {{ method_field('POST') }}
   
   <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"> &times; </span> </button>
      <h3 class="modal-title"></h3>
   </div>
        
<div class="modal-body">
  
  <div class="form-group">
    <label for="kode" class="col-md-3 control-label">Kode Produk</label>
    <div class="col-md-6">
      <input id="kode" type="number" class="form-control" name="kode" autofocus required>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="nama" class="col-md-3 control-label">Nama Produk</label>
    <div class="col-md-6">
      <input id="nama" type="text" class="form-control" name="nama" required>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="nama_struk" class="col-md-3 control-label">Nama Struk</label>
    <div class="col-md-6">
      <input id="nama_struk" type="text" class="form-control" name="nama_struk" required>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="status" class="col-md-3 control-label">Satuan</label>
    <div class="col-md-6">
      <select id="status" type="text" class="form-control" name="satuan" required>
        <option value=""> -- Pilih Satuan-- </option>
        <option value="Pcs">Pcs</option>
        <option value="Kg">Kg</option>
        <option value="Grm">Grm</option>
        <option value="Liter">Liter</option>
        <option value="Pack">Pack</option>
        <option value="Box/Dus">Box/Dus</option>
        <option value="Rcg">Rcg</option>
        </select>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="status" class="col-md-3 control-label">Status</label>
    <div class="col-md-6">
      <select id="status" type="text" class="form-control" name="status" required>
        <option value=""> -- Pilih Status-- </option>
        @foreach($status as $list)
        <option value="{{ $list->id_status }}">{{ $list->keterangan }}</option>
        @endforeach
      </select>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="supplier" class="col-md-3 control-label">Supplier</label>
    <div class="col-md-6">
      <select id="supplier" type="text" class="form-control" name="supplier" required>
        <option value=""> -- Pilih Supplier-- </option>
        @foreach($supplier as $list)
        <option value="{{ $list->id_supplier }}">{{ $list->nama }}</option>
        @endforeach
      </select>
      <span class="help-block with-errors"></span>
    </div>
  </div>

  <div class="form-group">
    <label for="kategori" class="col-md-3 control-label">Kategori</label>
    <div class="col-md-6">
      <select id="kategori" type="text" class="form-control" name="kategori" required>
        <option value=""> -- Pilih Kategori-- </option>
        @foreach($kategori as $list)
        <option value="{{ $list->id_kategori}}">{{ $list->nama_kategori }}</option>
        @endforeach
      </select>
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
