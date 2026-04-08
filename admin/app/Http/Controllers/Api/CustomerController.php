<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        

        $canPayLater = (bool) ($user->pay_later ?? false);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $user->id,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'wallet_balance' => (float) ($user->credit_balance ?? 0),
                'company_name' => $user->company_name ?? null,
                'address_line1' => $user->company_address_line1 ?? null,
                'address_line2' => $user->company_address_line2 ?? null,
                'city' => $user->company_city ?? null,
                'country' => $user->company_country ?? null,
                'postcode' => $user->company_zip_code ?? null,
                'vat_number' => $user->vat_number ?? null,
                'your_name' => $user->contact_person_name ?? null,
                'rep_name' => $user?->salesPerson?->name ?? null,
                'rep_email' => $user?->salesPerson?->email ?? null,
                'rep_mobile' => $user?->salesPerson?->phone ?? null,
                'sales_rep_name' => $user->sales_rep_name ?? null,
                'rep_code' => $user->rep_code ?? null,
                'pay_later_allowed' => $canPayLater,
            ],
        ]);
    }

    public function updateCompanyDetails(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' =>  ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'vat_number' => ['nullable', 'string', 'max:100'],
            'your_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'company_name' => $request->company_name,
            'company_address_line1' => $request->address_line1,
            'company_address_line2' => $request->address_line2,
            'company_city' => $request->city,
            'company_country' => $request->country,
            'company_zip_code' => $request->postcode,
            'phone' => $request->filled('contact_number') ? $request->contact_number : null,
            'vat_number' => $request->filled('vat_number') ? $request->vat_number : null,
            'contact_person_name' => $request->filled('your_name') ? $request->your_name : null,
        ]);
      

        return response()->json([
            'success' => true,
            'message' => 'Company details updated successfully',
            'customer' => [
                'id' => $user->id,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
                'phone' => $user->phone ?? null,
                'wallet_balance' => (float) ($user->credit_balance ?? 0),
                'company_name' => $user->company_name ?? null,
                'address_line1' => $user->company_address_line1 ?? null,
                'address_line2' => $user->company_address_line2 ?? null,
                'city' => $user->company_city ?? null,
                'country' => $user->company_country ?? null,
                'postcode' => $user->company_zip_code ?? null,
                'vat_number' => $user->vat_number ?? null,
                'your_name' => $user->contact_person_name ?? null,
            ],
        ]);
    }

    public function walletTransactions(Request $request)
    {
        $user = $request->user();

        $rows = WalletTransaction::query()
            ->leftJoin('orders', 'orders.id', '=', 'wallet_transactions.order_id')
            ->where('wallet_transactions.customer_id', $user->id)
            ->orderByDesc('wallet_transactions.created_at')
            ->select([
                'wallet_transactions.id',
                'wallet_transactions.order_id',
                'wallet_transactions.amount',
                'wallet_transactions.type',
                'wallet_transactions.description',
                'wallet_transactions.balance_after',
                'wallet_transactions.created_at',
                'orders.order_number',
                'orders.total_amount as order_total_amount',
            ])
            ->get();

        return response()->json([
            'success' => true,
            'transactions' => $rows->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'order_id' => $row->order_id ? (int) $row->order_id : null,
                    'order_number' => $row->order_number !== null ? (string) $row->order_number : null,
                    'order_total_amount' => $row->order_total_amount !== null ? (float) $row->order_total_amount : null,
                    'amount' => (float) ($row->amount ?? 0),
                    'type' => (string) ($row->type ?? ''),
                    'description' => $row->description !== null ? (string) $row->description : null,
                    'balance_after' => $row->balance_after !== null ? (float) $row->balance_after : null,
                    'created_at' => optional($row->created_at)?->toISOString(),
                ];
            })->values(),
        ]);
    }
}


