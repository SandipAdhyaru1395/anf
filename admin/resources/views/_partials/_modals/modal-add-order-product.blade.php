<!-- Add/Edit Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addItemModalTitle">Add New Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="itemForm" method="post" action="{{ route('order.item.create') }}">
          @csrf
          <div class="modal-body">
            <input type="hidden" id="itemId" name="id">
            <input type="hidden" id="orderId" name="order_id" value="{{ $order->id }}">
            
            <!-- Product Selection Section -->
            <div id="productSelectionSection">
              <div class="row">
                <div class="col-12 mb-3">
                  <label class="form-label">Product <span class="text-danger">*</span></label>
                  <select class="form-select select2 @error('product_id','addItemModal') is-invalid @enderror" id="productSelect" name="product_id" data-ajax-url="{{ route('product.search.ajax') }}" data-placeholder="Search product...">
                  </select>
                  @error('product_id','addItemModal')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="form-label">Quantity <span class="text-danger">*</span></label>
                  <input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control @error('quantity','addItemModal') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}">
                  @error('quantity','addItemModal')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                  <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control @error('unit_price','addItemModal') is-invalid @enderror" id="unitPrice" name="unit_price" value="{{ old('unit_price') }}">
                  @error('unit_price','addItemModal')
                    <span class="text-danger">{{ $message }}</span>
                  @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                  <label class="form-label">Total Price</label>
                  <input type="text" class="form-control" id="totalPrice" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitButton">Save Item</button>
          </div>
        </form>
      </div>
    </div>
  </div>