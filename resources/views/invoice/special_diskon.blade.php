
<div class="col-xs-6">
   <div class="box ">
      <div class="box-header">
      <h3 class="box-title">Spesial Diskon</h3>        
            <button type="button" onclick="showProduct()"class="btn btn-primary produk pull-right">+</button>
      </div> 
      
      <form class="form-spesial-diskon" action="post">
         {{csrf_field()}}
         <input type="hidden" name ="kode" class="input-spesial-diskon">
         <input type="hidden" name ="id" value="{{$id}}">
      </form>

      <div class="box-body">
      <form class="form-spesial" method="post">
      {{csrf_field()}}
      <table class="table table-striped spesial-diskon">
      
            <thead>
            <tr>
               <th style="width: 10px">No</th>
               <th>Barcode</th>
               <th>Nama Produk</th>
               <th style="width: 40px">Diskon</th>
               <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
               
            </tbody>
      </table>
      </form>
      </div>
   
   </div>
</div>






