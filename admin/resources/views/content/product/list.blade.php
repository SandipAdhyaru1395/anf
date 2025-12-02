@extends('layouts/layoutMaster')

@section('title', 'Product List')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss','resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',])
@endsection

@section('page-script')
@vite(['resources/assets/js/product-list.js'])
<script>
  function changeStatus(id, status) {
      if (id) {
        var publishObj = { 0 : 'publish' , 1 : 'unpublish'};
        Swal.fire({
          title: 'Are you sure you want to ' + publishObj[status] + ' this product?',
          // text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, ' + publishObj[status] + ' it!',
          customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false
        }).then(function (result) {
          if(result.isConfirmed){
            window.location.href = baseUrl + 'product/status/change/' + id;
          }
        });
      }
    }
  function deleteProduct(id) {
      if (id) {
        Swal.fire({
          title: 'Are you sure?',
          text: "You won't be able to revert this!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!',
          customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
          },
          buttonsStyling: false
        }).then(function (result) {
          if(result.isConfirmed){
            window.location.href = baseUrl + 'product/delete/' + id;
          }
        });
      }
    }
</script>
@endsection

@section('content')
<!-- Product List Widget -->
<div class="card mb-6">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Total Products</p>
              <h4 class="mb-1">{{ $total_products_count }}</h4>
            </div>
            <span class="avatar me-sm-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-cube icon-28px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-6" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Active Products</p>
              <h4 class="mb-1">{{ $active_products_count }}</h4>
            </div>
            <span class="avatar p-2 me-lg-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-checks icon-28px text-heading"></i></span>
            </span>
          </div>
          <hr class="d-none d-sm-block d-lg-none" />
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3">
            <div>
              <p class="mb-1">Inactive Products</p>
              <h4 class="mb-1">{{ $inactive_products_count }}</h4>
            </div>
            <span class="avatar p-2 me-sm-6">
              <span class="avatar-initial rounded"><i
                  class="icon-base ti tabler-ban icon-28px text-heading"></i></span>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Product List Table -->
<div class="card">
  <div class="card-datatable">
    <table class="datatables-products table">
      <thead class="border-top">
        <tr>
          <th></th>
          <th></th>
          <th>product</th>
          <th>product code</th>
          <th>price</th>
          <th>status</th>
          <th>actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Import Product Modal -->
<div class="modal fade" id="importProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="importProductForm" method="POST" action="{{ route('product.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-4">
            <label class="form-label" for="importFile">Select File <span class="text-danger">*</span></label>
            <input type="file" class="form-control" id="importFile" name="importFile" accept=".csv" required>
            <div class="form-text">Supported format: CSV only. Convert Excel files to CSV before uploading.</div>
            @error('importFile')
              <span class="text-danger" role="alert">
                <strong>{{ $message }}</strong>
              </span>
            @enderror
          </div>
            <div class="alert alert-info">
            <h6 class="alert-heading">File Format Requirements:</h6>
            <ul class="mb-0">
              <li><strong>File Format:</strong> CSV files only. If you have an Excel file (.xlsx, .xls), please convert it to CSV first: In Excel, go to File > Save As > Choose "CSV (Comma delimited) (*.csv)"</li>
              <li><strong>Column Headers (in order):</strong> SR NO., Product Name, Product Code, Product Unit Code, Selling Price, Cost Price (Optional), Wallet Credit (Optional), Weight (Kg) (Optional), RRP (Optional), Expiry Date (Optional), Quantity, Product Unit, VAT Method, Description (Optional), Status (1 = Active / 0 = Inactive), Image, Category, Type ( Sub Category ), Brand, Brand Image</li>
              <li><strong>Required fields:</strong> Product Name, Product Code, Product Unit Code, Selling Price, Status, Brand</li>
              <li><strong>Optional fields:</strong> Cost Price, Wallet Credit, Weight (Kg), RRP, Expiry Date, Quantity, Product Unit, VAT Method, Description, Category, Type ( Sub Category ), Brand Image, Image</li>
              <li><strong>Status:</strong> Enter 1 for Active or 0 for Inactive</li>
              <li><strong>Category:</strong> Enter the main category name. If the category doesn't exist, it will be created automatically with Active status.</li>
              <li><strong>Type ( Sub Category ):</strong> Enter the subcategory name. If Category is provided, Type will be created under that Category if it doesn't exist. If Category is blank but Type is provided, the system will search for the Type as an existing subcategory - if found, new brands will be bound to it; if not found, an error will be shown.</li>
              <li><strong>Brand:</strong> Enter one or more brand names separated by commas (e.g., "Brand 1, Brand 2, Brand 3"). 
                <ul style="margin-top: 5px; margin-bottom: 0;">
                  <li>If the brand exists, it will be used to create the product.</li>
                  <li>If the brand doesn't exist:
                    <ul>
                      <li>If Category or Type is provided, the brand will be created automatically with Brand Image (if provided), linked to the Category/Type, and set to Active status.</li>
                      <li>If both Category and Type are blank, an error will be shown (brands cannot be created without a category).</li>
                    </ul>
                  </li>
                </ul>
              </li>
              <li><strong>Brand Image:</strong> Enter a full URL (e.g., https://example.com/brand-image.jpg). If URL doesn't start with http:// or https://, it will be automatically prefixed. This is used when creating new brands.</li>
              <li><strong>VAT Method:</strong> Enter the VAT Method name as it appears in your system (e.g., "20%", "Standard VAT", etc.). If it doesn't exist, it will be created automatically.</li>
              <li><strong>Expiry Date:</strong> Format must be dd/mm/yyyy or dd-mm-yyyy (e.g., 26/11/2026 or 26-11-2026)</li>
              <li><strong>Product Code & Product Unit Code:</strong> Must be unique. Scientific notation (e.g., 6.93633E+12) will be automatically converted.</li>
              <li><strong>Image:</strong> Enter a full URL (e.g., https://example.com/image.jpg). If URL doesn't start with http:// or https://, it will be automatically prefixed.</li>
              <li><strong>Important:</strong> All rows will be validated before any products are imported. If any row has errors, no products will be imported. Fix all errors and try again.</li>
            </ul>
          </div>
          <div class="mb-3">
            <a href="{{ route('product.import.sample') }}" class="btn btn-sm btn-label-secondary">
              <i class="icon-base ti tabler-download me-1"></i>Download Sample CSV
            </a>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Import Products</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
