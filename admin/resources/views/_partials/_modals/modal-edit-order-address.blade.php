<!-- Edit Shipping Address Modal -->
<div class="modal fade" id="editAddress" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-6">
          <h4 class="address-title mb-2">Edit Order Address</h4>
          <p class="address-subtitle">Update address for this order</p>
        </div>
        <form id="editAddressForm" method="post" action="{{ route('order.update') }}" class="row g-6">
          @csrf
          <input type="hidden" name="id" value="{{ $order->id }}">
          <div class="col-12 form-control-validation">
            <label class="form-label" for="branch_name">Branch Name</label>
            <input type="text" id="branch_name" name="branch_name" value="{{ old('branch_name') ?? $order->branch_name }}" class="form-control" placeholder="Branch Name" />
            @error('branch_name', 'editAddressModal')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 form-control-validation">
            <label class="form-label" for="address_line1">Address Line 1</label>
            <input type="text" id="address_line1" name="address_line1" value="{{ old('address_line1') ?? $order->address_line1 }}" class="form-control" placeholder="12, Business Park" />
            @error('address_line1', 'editAddressModal')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12">
            <label class="form-label" for="address_line2">Address Line 2</label>
            <input type="text" id="address_line2" name="address_line2" value="{{ old('address_line2') ?? $order->address_line2 }}" class="form-control" placeholder="Mall Road" />
            @error('address_line2', 'editAddressModal')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="city">City</label>
            <input type="text" id="city" name="city" value="{{ old('city') ?? $order->city }}" class="form-control" placeholder="Los Angeles" />
            @error('city', 'editAddressModal')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="country">Country</label>
            <input type="text" id="country" name="country" value="{{ old('country') ?? $order->country }}" class="form-control" placeholder="California" />
            @error('country', 'editAddressModal')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="zip_code">Zip Code</label>
            <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code') ?? $order->zip_code }}" class="form-control" placeholder="99950" />
            @error('zip_code', 'editAddressModal')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-3">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Address Modal -->