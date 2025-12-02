<style>
    /* Scoped to the Purchase view modal content */
    #purchase-view-modal-content table.table {
        border-collapse: collapse !important;
        border: 1px solid var(--bs-border-color) !important;
    }

    #purchase-view-modal-content table.table th,
    #purchase-view-modal-content table.table td {
        border: 1px solid var(--bs-border-color) !important;
    }
</style>

<div class="modal-body">
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    <div class="card border-0 shadow-none">
        <div class="card-body p-0">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-5">
                <div class="col">
                    <b>Supplier:</b>
                    {{ optional($purchase->supplier)->company ?? (optional($purchase->supplier)->full_name ?? 'N/A') }}
                </div>
                <div class="col">
                    <b>Deliver:</b>
                    {{ $purchase->deliver ? ucfirst(str_replace('_', ' ', $purchase->deliver)) : 'N/A' }}
                </div>
                <div class="col">
                    <b>Date:</b> {{ optional($purchase->date)->format('d/m/Y H:i') }}<br>
                </div>
                <div class="col">
                    <b>Reference:</b> {{ $purchase->reference_no ? '#PO' . $purchase->reference_no : 'N/A' }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Description</th>
                            <th style="width: 120px;">Quantity</th>
                            <th style="width: 120px;">Cost Price</th>
                            <th style="width: 120px;">VAT</th>
                            <th style="width: 120px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchase->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $item->product ? $item->product->name . ' - ' . $item->product->sku : '#' . $item->product_id }}
                                </td>
                                <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                <td>{{ $setting['currency_symbol'] ?? '' }}{{ number_format((float) ($item->unit_cost ?? 0), 2) }}</td>
                                <td>{{ $setting['currency_symbol'] ?? '' }}{{ number_format((float) ($item->unit_vat ?? 0), 2) }}</td>
                                <td>{{ $setting['currency_symbol'] ?? '' }}{{ number_format((float) ($item->subtotal ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td class="text-end fw-bold">Subtotal:</td>
                                <td class="text-end">{{ $setting['currency_symbol'] ?? '' }}{{ number_format((float) ($purchase->sub_total ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold">VAT:</td>
                                <td class="text-end">{{ $setting['currency_symbol'] ?? '' }}{{ number_format((float) ($purchase->vat ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold">Shipping Charge:</td>
                                <td class="text-end">{{ $setting['currency_symbol'] ?? '' }}{{ number_format((float) ($purchase->shipping_charge ?? 0), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold">
                                    {{ $setting['currency_symbol'] ?? '' }}{{ number_format((float) ($purchase->total_amount ?? 0), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-md-6 mt-5">
                    <div class="border bg-light rounded p-3 h-100">
                        <div class="fw-bold mb-2">Note:</div>
                        <div class="small text-black">{!! $purchase->note !!}</div>
                    </div>
                </div>
                <div class="col-md-6 mt-5">
                    <div class="border bg-light rounded p-3 h-100">
                        <div class="fw-bold mb-2">Created by:</div>
                        <div class="small text-black">
                            {{ $purchase->user ? $purchase->user->name : 'N/A' }}<br>
                            Date: {{ optional($purchase->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
