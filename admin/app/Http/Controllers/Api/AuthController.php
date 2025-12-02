<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\SyncUpdate;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
	public function login(Request $request)
	{
		$validated = $request->validate([
			'email' => 'required|email',
			'password' => 'required|string',
			'device_name' => 'nullable|string',
		]);

		// Include soft-deleted users to provide a clear error message
		$customer = Customer::withTrashed()->where('email', $validated['email'])->first();
		if ($customer && method_exists($customer, 'trashed') && $customer->trashed()) {
			return response()->json([
				'success' => false,
				'message' => 'Account has been deleted',
			], 410);
		}

		if (!$customer || !Hash::check($validated['password'], $customer->password)) {
			return response()->json([
				'success' => false,
				'message' => 'Invalid credentials',
			], 401);
		}

		if (!(int)($customer->is_active ?? 0)) {
			return response()->json([
				'success' => false,
				'message' => 'Account is inactive',
			], 403);
		}

		$deviceName = $validated['device_name'] ?? $request->userAgent() ?? 'api-client';
		$token = $customer->createToken($deviceName)->plainTextToken;

		// Update last_login without firing model events to avoid version increment
		DB::table('customers')->where('id', $customer->id)->update(['last_login' => now()]);

		// Get latest version for Customer model from sync_updates
		$customerVersion = 0;
		try {
			$customerVersion = (int)(SyncUpdate::query()->where('entity', 'Customer')->value('version') ?? 0);
		} catch (\Throwable $e) {
			try {
				$customerVersion = (int)(DB::table('sync_updates')->where('model', 'Customer')->value('version') ?? 0);
			} catch (\Throwable $e2) {
				$customerVersion = 0;
			}
		}

		return response()->json([
			'success' => true,
			'token' => $token,
			'customer' => [
				'id' => $customer->id,
				'name' => $customer->name,
				'email' => $customer->email,
			],
			'versions' => [
				'Customer' => $customerVersion,
			],
		]);
	}

	public function logout(Request $request)
	{
        
		$user = $request->user();
		if ($user && method_exists($user, 'currentAccessToken')) {
			$user->currentAccessToken()->delete();
		}
		return response()->json([
			'success' => true,
			'message' => 'Logged out',
		]);
	}

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'companyName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'mobile' => ['required', 'string', 'digits:10', 'unique:customers,phone'],
            'password' => ['required', 'string', 'min:6'],
            // address fields
            'addressLine1' => ['required', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:255'],
        ], [
            'email.required' => 'Please enter email',
            'email.unique' => 'Email already exists',
            'password.required' => 'Please enter password',
            'password.min' => 'Password must be more than 6 characters',
            'mobile.required' => 'Please enter mobile number',
            'mobile.digits' => 'Mobile number must be 10 digits',
            'mobile.unique' => 'Mobile number already exists',
            'companyName.required' => 'Please enter company name',
            'addressLine1.required' => 'Please enter address line 1',
            'city.required' => 'Please enter city',
            'zip_code.required' => 'Please enter postcode',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Create customer first (hash password)
        $customer = Customer::create([
            'email' => $data['email'],
            'phone' => $data['mobile'],
            'password' => bcrypt($data['password']),
            'approved_at' => null,
            'approved_by' => null,
            'is_active' => 1,
            'company_name' => $data['companyName'],
            'company_address_line1' => $data['addressLine1'],
            'company_address_line2' => $data['addressLine2'] ?? null,
            'company_city' => $data['city'],
            'company_country' => $data['country'] ?? null,
            'company_zip_code' => $data['zip_code'],
        ]);
    

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'customer_id' => $customer->id,
        ], 201);
    }
}


