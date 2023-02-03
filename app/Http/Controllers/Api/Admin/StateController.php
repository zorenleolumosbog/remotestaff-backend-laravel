<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Country;
use App\Models\Admin\State;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class StateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $states = State::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->with(['country'])
        ->with(['region' => function ($query) {
            $query->with(['country']);
        }])
        ->orWhereHas('country', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('short_desc', 'LIKE', "{$request->search}%");
                $query->orWhere('long_desc', 'LIKE', "{$request->search}%");
            });
        })
        ->orWhereHas('region', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('description', 'LIKE', "{$request->search}%");
            })
            ->orWhereHas('country', function (Builder $query) use ($request) {
                $query->when($request->search, function ($query) use ($request) {
                    $query->where('short_desc', 'LIKE', "{$request->search}%");
                    $query->orWhere('long_desc', 'LIKE', "{$request->search}%");
                });
            });
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : State::count());

        if( $states->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $states,
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
            'country_id' => [
                'nullable',
                'exists:tblm_country,id',
                Rule::requiredIf(!$request->region_id),
                Rule::requiredIf(Country::where('id', $request->country_id)->where('with_region', 0)->exists()),
                Rule::prohibitedIf(State::where('link_country_id', $request->country_id)
                                ->where('description', $request->description)
                                ->exists())
            ],
            'region_id' => [
                'nullable',
                'exists:tblm_region,id',
                Rule::requiredIf(Country::where('id', $request->country_id)->where('with_region', 1)->exists()),
                Rule::prohibitedIf(State::where('link_region_id', $request->region_id)
                                ->where('description', $request->description)
                                ->exists())
            ],
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'country_id.exists' => 'The Country is invalid.',
            'country_id.required' => 'The Country is required.',
            'country_id.prohibited' => 'The selected Country and State/Province already exists.',
            'region_id.exists' => 'The selected Region is invalid.',
            'region_id.required' => 'The Region is required.',
            'region_id.prohibited' => 'The selected Region and State/Province already exists.',
            'description.required' => 'The State is required.',
            'description.regex' => 'The State should not contain any special characters.',
            'description.max' => 'The State must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $states = State::create([
            'link_country_id' => $request->region_id ? null : $request->country_id,
            'link_region_id' => $request->region_id,
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $states,
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
        $states = State::
        with(['towncities' => function ($query) {
            $query->with(['barangays']);
        }])
        ->with(['country'])
        ->with(['region' => function ($query) {
            $query->with(['country']);
        }])
        ->where('id', $id)
        ->first();

        if( !$states) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $states,
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
        $states = State::find($id);

        if( !$states ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
        $validator = Validator::make($request->all(), [
            'country_id' => [
                'nullable',
                'exists:tblm_country,id',
                Rule::requiredIf(!$request->region_id),
                Rule::requiredIf(Country::where('id', $request->country_id)->where('with_region', 0)->exists()),
                Rule::prohibitedIf(State::where('link_country_id', $request->country_id)
                                ->where('description', $request->description)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'region_id' => [
                'nullable',
                'exists:tblm_region,id',
                Rule::requiredIf(Country::where('id', $request->country_id)->where('with_region', 1)->exists()),
                Rule::prohibitedIf(State::where('link_region_id', $request->region_id)
                                ->where('description', $request->description)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'country_id.exists' => 'The Country is invalid.',
            'country_id.required' => 'The Country is required.',
            'country_id.prohibited' => 'The selected Country and State/Province already exists.',
            'region_id.exists' => 'The selected Region is invalid.',
            'region_id.required' => 'The Region is required.',
            'region_id.prohibited' => 'The selected Region and State/Province already exists.',
            'description.required' => 'The State is required.',
            'description.regex' => 'The State should not contain any special characters.',
            'description.max' => 'The State must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        State::where('id', $id)->update([
            'link_country_id' => $request->region_id ? null : $request->country_id,
            'link_region_id' => $request->region_id,
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => State::find($id)
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
        $states = State::find($id);

        if( !$states ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $states->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
