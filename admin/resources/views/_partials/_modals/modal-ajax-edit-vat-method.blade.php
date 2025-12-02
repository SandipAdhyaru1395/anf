<div class="modal fade" id="ajaxEditVATMethodModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-simple">
      <div class="modal-content">
          <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-6">
                  <h4 class="mb-2">Edit VAT Method</h4>
              </div>
              <form id="ajaxEditVATMethodForm" method="POST" class="row g-6"
                  action="{{ route('settings.vatMethod.update') }}">
                  @csrf
                  <div class="row">
                      <input type="hidden" name="id" id="id" value="{{ old('id') }}">
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="vatName">Name</label>
                          <input type="text" id="vatName" name="vatName" class="form-control" placeholder="Name"
                              value="{{ old('vatName') }}" />
                          @error('vatName', 'editVatModal')
                              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                          @enderror
                      </div>
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="vatType">Type</label>
                          <select id="vatType" name="vatType" class="form-select">
                              <option value="Percentage" @selected(old('vatType') == 'Percentage')>Percentage</option>
                              <option value="Fixed" @selected(old('vatType') == 'Fixed')>Fixed</option>
                          </select>
                      </div>
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="vatAmount">Amount</label>
                          <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" id="vatAmount"
                              name="vatAmount" class="form-control" placeholder="Amount" value="{{ old('vatAmount') }}" />
                          @error('vatAmount', 'editVatModal')
                              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                          @enderror
                      </div>
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="vatStatus">Status</label>
                          <select id="vatStatus" name="vatStatus" class="form-select">
                              <option value="Active" @selected(old('vatStatus') == 'Active')>Active</option>
                              <option value="Inactive" @selected(old('vatStatus') == 'Inactive')>Inactive</option>
                          </select>
                          @error('vatStatus', 'editVatModal')
                              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                          @enderror
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
