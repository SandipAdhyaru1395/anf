<div class="modal fade" id="ajaxEditUnitModal" tabindex="-1">
  <div class="modal-dialog modal-md modal-simple">
      <div class="modal-content">
          <div class="modal-body">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              <div class="text-center mb-6">
                  <h4 class="mb-2">Edit Unit</h4>
              </div>
              <form id="ajaxEditUnitForm" method="POST" class="row g-6"
                  action="{{ route('settings.unit.update') }}">
                  @csrf
                  <div class="row">
                      <input type="hidden" name="id" id="id" value="{{ old('id') }}">
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="unitName">Name</label>
                          <input type="text" id="unitName" name="unitName" class="form-control" placeholder="Name"
                              value="{{ old('unitName') }}" />
                          @error('unitName', 'editUnitModal')
                              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
                          @enderror
                      </div>
                      <div class="col-12 mb-6 form-control-validation">
                          <label class="form-label" for="unitStatus">Status</label>
                          <select id="unitStatus" name="unitStatus" class="form-select">
                              <option value="Active" @selected(old('unitStatus') == 'Active')>Active</option>
                              <option value="Inactive" @selected(old('unitStatus') == 'Inactive')>Inactive</option>
                          </select>
                          @error('unitStatus', 'editUnitModal')
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


