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
use App\Services\CustomerRegistrationAdminNotifyService;
use App\Services\CustomerWelcomeEmailService;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rule;

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

		// Pending must be checked before (int) cast: (int) null === 0 in PHP.
		if ($customer->is_approved === null) {
			return response()->json([
				'success' => false,
				'code' => 'not_approved',
				'message' => 'Your account is not approved yet. Please wait for an administrator to approve your registration before signing in.',
			], 403);
		}

		if ((int) $customer->is_approved === 0) {
			return response()->json([
				'success' => false,
				'code' => 'rejected',
				'message' => 'Your registration was not approved. Please contact support if you have questions.',
			], 403);
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

    public function register(Request $request, CustomerWelcomeEmailService $customerWelcomeEmailService, CustomerRegistrationAdminNotifyService $customerRegistrationAdminNotifyService)
    {
        $request->merge([
            'mobile' => PhoneNormalizer::normalize($request->input('mobile')) ?? '',
        ]);

        $validator = Validator::make($request->all(), [
            'companyName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'mobile' => ['required', 'string', 'min:10', 'max:20', 'regex:/^[a-zA-Z0-9]+$/', Rule::unique('customers', 'phone')],
            'password' => ['required', 'string', 'min:6'],
            // address fields
            'addressLine1' => ['required', 'string', 'max:255'],
            'addressLine2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:255'],
            'vatNumber' => ['nullable', 'string', 'max:100'],
            'eoriNumber' => ['nullable', 'string', 'max:100'],
            'isPartOfGroup' => ['nullable', Rule::in(['yes', 'no'])],
            'businessType' => ['nullable', Rule::in(['Wholesaler', 'Distributor', 'Retailer', 'Online retailer', 'Vape shop'])],
            'averageMonthlySpendExVat' => ['nullable', 'string', 'max:255'],
            'storesServicedCount' => ['nullable', 'integer', 'min:0'],
            'yourName' => ['nullable', 'string', 'max:255'],
            'salesRepName' => ['nullable', 'string', 'max:255'],
            'repCode' => ['nullable', 'string', 'max:100'],
        ], [
            'email.required' => 'Please enter email',
            'email.unique' => 'Email already exists',
            'password.required' => 'Please enter password',
            'password.min' => 'Password must be more than 6 characters',
            'mobile.required' => 'Please enter mobile number',
            'mobile.min' => 'Mobile must be at least 10 characters.',
            'mobile.max' => 'Mobile must be at most 20 characters.',
            'mobile.regex' => 'Mobile must contain only letters and numbers.',
            'mobile.unique' => 'Mobile number already exists',
            'companyName.required' => 'Please enter company name',
            'addressLine1.required' => 'Please enter address line 1',
            'city.required' => 'Please enter city',
            'zip_code.required' => 'Please enter postcode',
            'isPartOfGroup.required' => 'Please select if you are part of a group',
            'businessType.required' => 'Please select type of business',
            'yourName.required' => 'Please enter your name',
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
            'is_approved' => null,
            'is_active' => 0,
            'company_name' => $data['companyName'],
            'company_address_line1' => $data['addressLine1'],
            'company_address_line2' => $data['addressLine2'] ?? null,
            'company_city' => $data['city'],
            'company_country' => $data['country'] ?? null,
            'company_zip_code' => $data['zip_code'],
            'vat_number' => $data['vatNumber'] ?? null,
            'eori_number' => $data['eoriNumber'] ?? null,
            'is_part_of_group' => $data['isPartOfGroup'] === 'yes' ? 1 : 0,
            'business_type' => $data['businessType'],
            'average_monthly_spend_ex_vat' => $data['averageMonthlySpendExVat'] ?? null,
            'stores_serviced_count' => $data['storesServicedCount'],
            'contact_person_name' => $data['yourName'],
            'sales_rep_name' => !empty($data['salesRepName'] ?? null)
                ? trim((string) $data['salesRepName'])
                : null,
            'rep_code' => !empty($data['repCode'] ?? null)
                ? trim((string) $data['repCode'])
                : null,
            'pay_later' => false,
        ]);
        $customerWelcomeEmailService->send($customer);
        $customerRegistrationAdminNotifyService->send($customer);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'customer_id' => $customer->id,
        ], 201);
    }

    /**
     * POST /forgot-password — queue password reset email (generic response for privacy).
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::broker('customers')->sendResetLink($request->only('email'));

        return response()->json([
            'success' => true,
            'message' => 'If an account exists for that email, we have sent password reset instructions.',
        ]);
    }

    /**
     * POST /reset-password — complete reset after customer follows email link.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Customer $user, string $password) {
                $user->password = $password;
                $user->save();
                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset. You can log in with your new password.',
            ]);
        }

        $message = $status === Password::INVALID_TOKEN
            ? 'This password reset link is invalid or has expired. Please request a new one.'
            : 'Unable to reset password. Please try again.';

        return response()->json([
            'success' => false,
            'message' => $message,
        ], 422);
    }
}



