<!-- View Payments Modal -->
<div class="modal fade" id="viewPaymentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="viewPaymentsModalTitle">VIEW PAYMENTS</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead>
              <tr style="background-color: var(--bs-primary); color: white;">
                <th style="color: white;">Date</th>
                <th style="color: white;">Reference No</th>
                <th style="color: white;">Amount</th>
                <th style="color: white;">Paid by</th>
                <th style="color: white;">Actions</th>
              </tr>
            </thead>
            <tbody id="viewPaymentsTableBody">
              <tr>
                <td colspan="5" class="text-center">Loading payments...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

