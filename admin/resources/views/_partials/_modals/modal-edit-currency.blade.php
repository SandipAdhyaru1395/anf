<div class="modal fade" id="ajaxEditCurrencyModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Currency</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-4">Please fill in the information below. The field labels marked with <span class="text-danger">*</span> are required input fields.</p>
        <form id="ajaxEditCurrencyForm" method="POST" action="{{ route('settings.currency.update') }}">
          @csrf
          <input type="hidden" name="id" id="currency_id" value="{{ old('id') }}">
          <div class="mb-3 form-control-validation">
            <label class="form-label" for="edit_currency_code">Currency Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_currency_code" name="currency_code" placeholder="e.g. USD" maxlength="10" />
            @error('currency_code', 'editCurrencyModal')
              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <div class="mb-3 form-control-validation">
            <label class="form-label" for="edit_currency_name">Currency Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_currency_name" name="currency_name" placeholder="e.g. US Dollar" />
            @error('currency_name', 'editCurrencyModal')
              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <div class="mb-3 form-control-validation">
            <label class="form-label" for="edit_symbol">Symbol <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_symbol" name="symbol" placeholder="e.g. $" maxlength="20" />
            @error('symbol', 'editCurrencyModal')
              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <div class="mb-3 form-control-validation">
            <label class="form-label" for="edit_exchange_rate">Exchange Rate <span class="text-danger">*</span></label>
            <input type="number" step="0.000001" min="0" class="form-control" id="edit_exchange_rate" name="exchange_rate" placeholder="e.g. 1.000000" />
            @error('exchange_rate', 'editCurrencyModal')
              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Currency</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
