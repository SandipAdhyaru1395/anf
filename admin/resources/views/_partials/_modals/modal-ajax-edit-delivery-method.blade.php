<div class="modal fade" id="ajaxEditDeliveryMethodModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-simple">
      <div class="modal-content">
          <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-6">
                  <h4 class="mb-2">Edit Delivery Method</h4>
              </div>
              <form id="ajaxEditDeliveryMethodForm" method="POST" class="row g-6"
                  action="{{ route('settings.deliveryMethod.update') }}">
                  @csrf
                
                  <div class="row">
                      <input type="hidden" name="id" id="id" value="{{ old('id') }}">
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="dmName">Name</label>
                          <input type="text" id="dmName" name="dmName" class="form-control" placeholder="Name"
                              value="{{ old('dmName') }}" />
                          @error('dmName', 'editModal')
                              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                          @enderror
                      </div>
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="dmTime">Delivery Time</label>
                          <input type="text" id="dmTime" name="dmTime" class="form-control"
                              placeholder="Delivery Time" value="{{ old('dmTime') }}" />
                          @error('dmTime', 'editModal')
                              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                          @enderror
                      </div>
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="dmPrice">Rate</label>
                          <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" id="dmPrice"
                              name="dmPrice" class="form-control" placeholder="Rate" value="{{ old('dmPrice') }}" />
                          @error('dmPrice', 'editModal')
                              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                          @enderror
                      </div>
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="dmStatus">Status</label>
                          <select id="dmStatus" name="dmStatus" class="select2 form-select"
                              aria-label="Default select example">
                              <option value="Active" @selected(old('dmStatus') == 'Active')>Active</option>
                              <option value="Inactive" @selected(old('dmStatus') == 'Inactive')>Inactive</option>
                          </select>
                      </div>
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="dmSortOrder">Sort Order (optional)</label>
                          <input type="text" onkeypress="return /^[0-9]+$/.test(event.key)" id="dmSortOrder"
                              name="dmSortOrder" class="form-control" placeholder="e.g. 1" value="{{ old('dmSortOrder') }}" />
                      </div>
                      <div class="col-12 text-center mb-6">
                          <button type="submit" class="btn btn-primary me-3">Submit</button>
                          <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                              aria-label="Close">Cancel</button>
                      </div>
                  </div>
              </form>
          </div>
      </div>
  </div>
</div>