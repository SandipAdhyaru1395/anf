<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddDeliveryMethod" aria-labelledby="offcanvasAddDeliveryMethodLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasAddDeliveryMethodLabel" class="offcanvas-title">Add Delivery Method</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="pt-0" id="addDeliveryMethodForm" method="POST" action="{{ route('settings.deliveryMethod.store') }}">
      @csrf
      <div class="row">
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="dmName">Name</label>
          <input type="text" class="form-control" id="dmName" name="dmName" placeholder="Name" value="{{ old('dmName') }}" />
          @error('dmName', 'addModal')
          <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
          @enderror
        </div>
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="dmTime">Delivery Time</label>
          <input type="text" class="form-control" id="dmTime" name="dmTime" placeholder="Delivery Time" value="{{ old('dmTime') }}" />
          @error('dmTime', 'addModal')
          <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
          @enderror
        </div>
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="dmPrice">Rate</label>
          <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control" id="dmPrice" name="dmPrice" placeholder="Rate" value="{{ old('dmPrice') }}" />
          @error('dmPrice', 'addModal')
          <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
          @enderror
        </div>
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="dmStatus">Status</label>
          <select id="dmStatus" name="dmStatus" class="form-select">
            <option value="Active" @selected(old('dmStatus') == 'Active')>Active</option>
            <option value="Inactive" @selected(old('dmStatus') == 'Inactive')>Inactive</option>
          </select>
        </div>
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="dmSortOrder">Sort Order (optional)</label>
          <input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" class="form-control" id="dmSortOrder" name="dmSortOrder" placeholder="e.g. 1" value="{{ old('dmSortOrder') }}" />
        </div>
        <div class="col-12 text-center mb-6">
          <button type="submit" class="btn btn-primary me-3 data-submit">Submit</button>
          <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

