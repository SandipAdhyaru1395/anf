<style>
  /* Scoped to the QA view modal content */
  #qa-view-modal-content table.table {
    border-collapse: collapse !important;
    border: 1px solid var(--bs-border-color) !important;
  }
  #qa-view-modal-content table.table th,
  #qa-view-modal-content table.table td {
    border: 1px solid var(--bs-border-color) !important;
  }
</style>

  <div class="modal-body">
  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    <div class="card border-0 shadow-none">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <div class="row">
                      <div class="col">
                        <b>Date:</b> {{ optional($adjustment->date)->format('d/m/Y H:i') }}<br>
                      </div>
                    </div> 
                    <div class="row">
                      <div class="col">
                        <b>Reference:</b> {{ $adjustment->reference_no ? '#QA' . $adjustment->reference_no : 'N/A' }}
                      </div>
                    </div>                   
                </div>
                {{-- <div class="text-end">
            <button type="button" class="btn btn-label-secondary btn-sm" onclick="window.print()">
              <i class="icon-base ti tabler-printer me-1"></i> Print
            </button>
          </div> --}}
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Description</th>
                            <th style="width: 120px;">Type</th>
                            <th style="width: 120px;">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($adjustment->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $item->product ? $item->product->name . ' - ' . $item->product->sku : '#' . $item->product_id }}
                                </td>
                                <td>{{ ucfirst($item->type) }}</td>
                                <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row mt-5">
                <div class="col-md-6 mt-5">
                    <div class="border bg-light rounded p-3 h-100">
                        <div class="fw-bold mb-2">Note:</div>
                        <div class="small text-black">{!! $adjustment->note !!}</div>
                    </div>
                </div>
                <div class="col-md-6 mt-5">
                    <div class="border bg-light rounded p-3 h-100">
                        <div class="fw-bold mb-2">Created by:</div>
                        <div class="small text-black">
                            {{ $adjustment->user ? $adjustment->user->name : 'N/A' }}<br>
                            Date: {{ optional($adjustment->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
