<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddVATMethod" aria-labelledby="offcanvasAddVATMethodLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="offcanvasAddVATMethodLabel" class="offcanvas-title">Add VAT Method</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form class="pt-0" id="addVATMethodForm" method="POST" action="{{ route('settings.vatMethod.store') }}">
      @csrf
      <div class="row">
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="vatName">Name</label>
          <input type="text" class="form-control" id="vatName" name="vatName" placeholder="Name" value="{{ old('vatName') }}" />
          @error('vatName', 'addVatModal')
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
          <input type="text" onkeypress="return /^[0-9.]+$/.test(event.key)" class="form-control" id="vatAmount" name="vatAmount" placeholder="Amount" value="{{ old('vatAmount') }}" />
          @error('vatAmount', 'addVatModal')
          <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
          @enderror
        </div>
        <div class="col-12 mb-6 form-control-validation">
          <label class="form-label" for="vatStatus">Status</label>
          <select id="vatStatus" name="vatStatus" class="form-select">
            <option value="Active" @selected(old('vatStatus') == 'Active')>Active</option>
            <option value="Inactive" @selected(old('vatStatus') == 'Inactive')>Inactive</option>
          </select>
          @error('vatStatus', 'addVatModal')
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
