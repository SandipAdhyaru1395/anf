<div class="modal fade" id="modalAddCurrency" tabindex="-1" aria-labelledby="modalAddCurrencyLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAddCurrencyLabel">ADD CURRENCY</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-4">Please fill in the information below. The field labels marked with <span class="text-danger">*</span> are required input fields.</p>
        <form id="addCurrencyForm" method="POST" action="{{ route('settings.currency.store') }}">
          @csrf
          <div class="mb-3 form-control-validation">
            <label class="form-label" for="currency_code">Currency Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="currency_code" name="currency_code" placeholder="e.g. USD" value="{{ old('currency_code') }}" maxlength="10" />
            @error('currency_code', 'addCurrencyModal')
              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <div class="mb-3 form-control-validation">
            <label class="form-label" for="currency_name">Currency Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="currency_name" name="currency_name" placeholder="e.g. US Dollar" value="{{ old('currency_name') }}" />
            @error('currency_name', 'addCurrencyModal')
              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <div class="mb-3 form-control-validation">
            <label class="form-label" for="symbol">Symbol <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="symbol" name="symbol" placeholder="e.g. $" value="{{ old('symbol') }}" maxlength="20" />
            @error('symbol', 'addCurrencyModal')
              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <div class="mb-3 form-control-validation">
            <label class="form-label" for="exchange_rate">Exchange Rate <span class="text-danger">*</span></label>
            <input type="number" step="0.000001" min="0" class="form-control" id="exchange_rate" name="exchange_rate" placeholder="e.g. 1.000000" value="{{ old('exchange_rate', '1') }}" />
            @error('exchange_rate', 'addCurrencyModal')
              <span class="text-danger" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Currency</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
