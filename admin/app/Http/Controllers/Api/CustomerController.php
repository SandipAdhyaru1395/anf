<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        

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
                'rep_name' => $user?->salesPerson?->name ?? null,
                'rep_email' => $user?->salesPerson?->email ?? null,
                'rep_mobile' => $user?->salesPerson?->phone ?? null
            ],
        ]);
    }

    public function updateCompanyDetails(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'city' =>  ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:255'],
        ]);

        // Update customer company name
        $user->update([
            'company_name' => $request->company_name,
            'company_address_line1' => $request->address_line1,
            'company_address_line2' => $request->address_line2,
            'company_city' => $request->city,
            'company_country' => $request->country,
            'company_zip_code' => $request->postcode,
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
            ],
        ]);
    }
}


