<!-- Edit Shipping Branch Modal -->
<div class="modal fade" id="addCustomerBranch" tabindex="-1">
  <div class="modal-dialog modal-lg modal-simple modal-add-new-branch">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        <div class="text-center mb-6">
          <h4 class="branch-title mb-2">Add Branch</h4>
        </div>
        <form method="post" action="{{ route('customer.branch.store') }}" class="row g-6" id="addCustomerBranchForm">
          @csrf
          <input type="hidden" name="customer_id" value="{{ $customer->id ?? '' }}">
          <input type="hidden" name="id" value="">
          
          <div class="col-12 form-control-validation">
            <label class="form-label" for="name">Branch Name <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="name" name="name" value="{{ old('name') ?? '' }}" class="form-control" placeholder="Branch name" />
            @error('name','addBranch')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 form-control-validation">
            <label class="form-label" for="address_line1">Address Line 1 <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="address_line1" name="address_line1" value="{{ old('address_line1') ?? '' }}" class="form-control" placeholder="12, Business Park" />
            @error('address_line1','addBranch')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12">
            <label class="form-label" for="address_line2">Address Line 2</label>
            <input type="text" autocomplete="off" id="address_line2" name="address_line2" value="{{ old('address_line2') ?? '' }}" class="form-control" placeholder="Mall Road" />
            @error('address_line2','addBranch')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="city">City <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="city" name="city" value="{{ old('city') ?? '' }}" class="form-control" placeholder="Los Angeles" />
            @error('city','addBranch')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="add_country">Country</label>
            <input type="text" autocomplete="off" id="country" name="country" value="{{ old('country') ?? '' }}" class="form-control" placeholder="United States" />
            @error('country','addBranch')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="col-12 col-md-6 form-control-validation">
            <label class="form-label" for="zip_code">Zip Code <span class="text-danger">*</span></label>
            <input type="text" autocomplete="off" id="zip_code" name="zip_code" value="{{ old('zip_code') ?? '' }}" class="form-control" placeholder="99950" />
            @error('zip_code','addBranch')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-3">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Branch Modal -->