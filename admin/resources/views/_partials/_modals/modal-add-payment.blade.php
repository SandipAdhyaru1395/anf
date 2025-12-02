<!-- Add Payment Modal -->
<style>
  .flatpickr-calendar.open {
    z-index: 9999 !important;
  }
</style>
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">ADD PAYMENT</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addPaymentForm" method="POST">
        @csrf
        <input type="hidden" name="order_id" id="payment_order_id">
        <div class="modal-body">
          <p class="mb-4">Please fill in the information below. The field labels marked with * are required input fields.</p>
          
          <div class="row">
            <div class="col-md-4 mb-4 form-control-validation">
              <label class="form-label" for="payment_date">Date <span class="text-danger">*</span></label>
              <input type="text" class="form-control flatpickr" id="payment_date" name="date">
            </div>
            <div class="col-md-4 mb-4 form-control-validation">
              <label class="form-label" for="payment_amount">Amount <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="payment_amount" onkeypress="return /^[0-9.]+$/.test(event.key)" name="amount" placeholder="0.00" autocomplete="off">
            </div>
            <div class="col-md-4 mb-4 form-control-validation">
              <label class="form-label" for="payment_method">Paying by <span class="text-danger">*</span></label>
              <select class="form-select" id="payment_method" name="payment_method">
                <option value="">Select Payment Method</option>
                <option value="Cash">Cash</option>
                <option value="Bank">Bank</option>
                <option value="Outstanding">Outstanding</option>
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label" for="payment_note">Note</label>
            <div class="form-control p-0">
              <div class="comment-toolbar border-0 border-bottom">
                <div class="d-flex justify-content-start">
                  <span class="ql-formats me-0">
                    <button class="ql-bold"></button>
                    <button class="ql-italic"></button>
                    <button class="ql-underline"></button>
                    <button class="ql-strike"></button>
                    <button class="ql-list" value="ordered"></button>
                    <button class="ql-list" value="bullet"></button>
                    <button class="ql-align" value=""></button>
                    <button class="ql-align" value="center"></button>
                    <button class="ql-align" value="right"></button>
                    <button class="ql-align" value="justify"></button>
                    <button class="ql-link"></button>
                    <button class="ql-image"></button>
                    <button class="ql-code-block"></button>
                    <button class="ql-clean"></button>
                  </span>
                </div>
              </div>
              <div id="payment_note_editor" class="comment-editor border-0 pb-6" style="min-height: 150px;"></div>
            </div>
            <input type="hidden" name="note" id="payment_note">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

