<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(),[
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
        ],[
            'name.required' => 'Please enter branch name',
            'address_line1.required' => 'Please enter address line 1',
            'city.required' => 'Please enter city',
            'zip_code.required' => 'Please enter zip code',
        ]);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator,'addBranch')->withInput();
        }
        try {
            DB::beginTransaction();

            $validatedData = $validator->validated();
            Branch::create($validatedData);

            DB::commit();

            Toastr::success('Branch saved successfully');
            return redirect()->back();
            

        } catch (\Exception $e) {
            DB::rollBack();

            Toastr::error('Something went wrong');
            return redirect()->back();
        }
    
    }


    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Request $request)
    {
        $branch = Branch::findOrFail($request->id);

        try {
            return response()->json([
                'success' => true,
                'branch' => $branch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load branch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:255',
        ],[
            'name.required' => 'Please enter branch name',
            'address_line1.required' => 'Please enter address line 1',
            'city.required' => 'Please enter city',
            'zip_code.required' => 'Please enter zip code',
        ]);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator,'editBranch')->withInput();
        }

        $branch = Branch::find($request->id);

        try {
            DB::beginTransaction();

            $validatedData = $validator->validated();
            $branch->update($validatedData);

            DB::commit();

            Toastr::success('Branch updated successfully');

            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Toastr::error('Something went wrong');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Branch $branch)
    {
        try {
            DB::beginTransaction();

            $branch->delete();

            DB::commit();

            Toastr::success('Branch deleted successfully');
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();
           Toastr::error('Something went wrong');
            return redirect()->back();
        }
    }
}
