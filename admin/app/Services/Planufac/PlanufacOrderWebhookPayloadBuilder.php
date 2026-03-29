<?php

namespace App\Services\Planufac;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

/**
 * Builds the JSON array payload for Planufac Custom Order App webhooks (order.created).
 */
class PlanufacOrderWebhookPayloadBuilder
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildEnvelope(Order $order): array
    {
        return [
            [
                'event' => 'order.created',
                'data' => [
                    'order' => $this->buildOrderPayload($order),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildOrderPayload(Order $order): array
    {
        $order->loadMissing(['items.product', 'customer.salesPerson']);

        $customer = $order->customer;
        $email = $customer?->email ?? '';

        $status = strtolower((string) ($order->status ?? 'new'));

        $poStored = trim((string) ($order->customer_po_number ?? ''));

        $lines = [];
        foreach ($order->items as $item) {
            $lines[] = $this->mapProductLine($item);
        }

        $netTotal = round((float) $order->subtotal + (float) $order->delivery_charge, 2);
        $grossTotal = round((float) $order->total_amount, 2);

        $createdSource = $order->created_at ?? $order->order_date;

        return [
            'id' => (int) $order->id,
            'number' => (int) $order->order_number,
            'created' => $createdSource
                ? Carbon::parse($createdSource)->utc()->format('Y-m-d\TH:i:s\Z')
                : now()->utc()->format('Y-m-d\TH:i:s\Z'),
            'status' => $status,
            'customer_id' => $customer ? (int) $customer->id : null,
            'company_name' => (string) ($customer?->company_name ?? ''),
            'phone' => (string) ($customer?->phone ?? ''),
            'email_addresses' => [
                'orders' => $email,
                'dispatches' => $email,
                'invoices' => $email,
            ],
            'created_by' => 'self',
            'delivery_date' => null,
            'reference' => null,
            'internal_note' => null,
            'customer_po_number' => $poStored !== '' ? $poStored : null,
            'customer_note' => $order->delivery_note,
            'shipping_type' => (string) ($order->delivery_method_name ?? ''),
            'shipping_address' => $this->shippingAddress($order),
            'billing_address' => $this->billingAddress($order, $customer),
            'order_lines' => $lines,
            'currency' => config('services.dna_payments.currency', 'GBP'),
            'net_total' => $netTotal,
            'gross_total' => $grossTotal,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapProductLine(OrderItem $item): array
    {
        $product = $item->product;
        $taxRate = (float) ($product?->vat_percentage ?? 0);
        $taxName = (string) ($product?->vat_method_name ?? ($taxRate > 0 ? 'Standard' : ''));

        $qty = (int) $item->quantity;
        $unitPrice = round((float) $item->unit_price, 4);
        $subTotal = round((float) $item->total_price, 2);
        $taxAmount = round($subTotal * ($taxRate / 100), 2);

        $vatMethodId = $product?->vat_method_id;

        return [
            'id' => (int) $item->id,
            'sku' => $product?->sku,
            'name' => (string) ($product?->name ?? 'Product #'.$item->product_id),
            'options' => null,
            'shipping' => false,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
            'sub_total' => $subTotal,
            'tax_rate_id' => $vatMethodId !== null && $vatMethodId !== '' ? (int) $vatMethodId : null,
            'tax_name' => 'Standard',
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'preorder_window_id' => null,
            'on_hold' => false,
            'invoiced' => 0,
            'paid' => 0,
            'dispatched' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function shippingAddress(Order $order): array
    {
        return [
            'company_name' => (string) ($order->branch_name ?? ''),
            'contact_name' => (string) ($order->branch_name ?? ''),
            'line1' => (string) ($order->address_line1 ?? ''),
            'line2' => $order->address_line2 ? (string) $order->address_line2 : null,
            'city' => (string) ($order->city ?? ''),
            'state' => null,
            'postal_code' => (string) ($order->zip_code ?? ''),
            'country' => (string) ($order->country ?? 'GB'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function billingAddress(Order $order, $customer): array
    {
        if ($customer && ($customer->company_address_line1 || $customer->company_city)) {
            return [
                'company_name' => (string) ($customer->company_name ?? ''),
                'contact_name' => (string) ($customer->company_name ?? ''),
                'line1' => (string) ($customer->company_address_line1 ?? ''),
                'line2' => $customer->company_address_line2 ? (string) $customer->company_address_line2 : null,
                'city' => (string) ($customer->company_city ?? ''),
                'postal_code' => (string) ($customer->company_zip_code ?? ''),
                'state' => null,
                'country' => (string) ($customer->company_country ?? $order->country ?? 'GB'),
            ];
        }

        return $this->shippingAddress($order);
    }
}
