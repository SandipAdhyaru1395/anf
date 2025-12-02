<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUnit" aria-labelledby="offcanvasAddUnitLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasAddUnitLabel" class="offcanvas-title">Add Unit</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="pt-0" id="addUnitForm" method="POST" action="{{ route('settings.unit.store') }}">
      @csrf
      <div class="row">
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="unitName">Name</label>
          <input type="text" class="form-control" id="unitName" name="unitName" placeholder="Name" value="{{ old('unitName') }}" />
          @error('unitName', 'addUnitModal')
          <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
          @enderror
        </div>
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="unitStatus">Status</label>
          <select id="unitStatus" name="unitStatus" class="form-select">
            <option value="Active" @selected(old('unitStatus') == 'Active')>Active</option>
            <option value="Inactive" @selected(old('unitStatus') == 'Inactive')>Inactive</option>
          </select>
          @error('unitStatus', 'addUnitModal')
          <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
          @enderror
        </div>
        <div class="col-12 text-center mb-6">
          <button type="submit" class="btn btn-primary me-3 data-submit">Submit</button>
          <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


