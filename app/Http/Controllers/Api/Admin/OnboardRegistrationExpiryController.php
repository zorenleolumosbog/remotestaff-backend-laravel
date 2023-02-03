<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\OnboardRegistrationExpiry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class OnboardRegistrationExpiryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $onboard_registration_expiry = OnboardRegistrationExpiry::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->with('onboard', function ($query) {
            $query->with(['onboardExpiry']);
        })
        ->with(['onboardBasicInfo'])
        ->orWhereHas('onboard', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('email', 'LIKE', "{$request->search}%");
            });
        })
        ->orWhereHas('onboardBasicInfo', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('reg_firstname', 'LIKE', "{$request->search}%");
                $query->orWhere('reg_lastname', 'LIKE', "{$request->search}%");
            });
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : OnboardRegistrationExpiry::count());

        if( $onboard_registration_expiry->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $onboard_registration_expiry,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'registrant_id' => 'required|exists:tblm_a_onboard_prereg,id|unique:tblm_onboard_registration_expiry,link_preregid',
            'description' => 'required|max:50'
        ],
        [
            'registrant_id.required' => 'The Onboard Registrant is required.',
            'registrant_id.exists' => 'The selected Onboard Registrant is invalid.',
            'registrant_id.unique' => 'The Onboard Registrant already exists.',
            'description.required' => 'The Onboard Registration Expiry description is required.',
            'description.max' => 'The Onboard Registration Expiry description must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $onboard_registration_expiry = OnboardRegistrationExpiry::create([
            'link_preregid' => $request->registrant_id,
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $onboard_registration_expiry,
				], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $onboard_registration_expiry = OnboardRegistrationExpiry::
        with(['onboard'])
        ->with(['onboardBasicInfo'])
        ->where('id', $id)
        ->first();

        if( !$onboard_registration_expiry) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $onboard_registration_expiry,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $onboard_registration_expiry = OnboardRegistrationExpiry::find($id);
        
        if( !$onboard_registration_expiry ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'registrant_id' => [
                'required',
                'exists:tblm_a_onboard_prereg,id',
                Rule::prohibitedIf(OnboardRegistrationExpiry::where('link_preregid', $request->registrant_id)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'description' => 'required|max:50'
        ],
        [
            'registrant_id.required' => 'The Onboard Registrant is required.',
            'registrant_id.exists' => 'The selected Onboard Registrant is invalid.',
            'registrant_id.prohibited' => 'The Onboard Registrant already exists.',
            'description.required' => 'The Onboard Registration Expiry description is required.',
            'description.max' => 'The Onboard Registration Expiry description must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        OnboardRegistrationExpiry::where('id', $id)->update([
            'link_preregid' => $request->registrant_id,
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => OnboardRegistrationExpiry::find($id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $onboard_registration_expiry = OnboardRegistrationExpiry::find($id);
        
        if( !$onboard_registration_expiry ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $onboard_registration_expiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
