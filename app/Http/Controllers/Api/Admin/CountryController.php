<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Country;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function geography(Request $request)
    {
        $countries = Country::
        withCount(['regions', 'states', 'towncities', 'barangays'])
        ->withRegions()
        ->withStates()
        ->when($request->search, function ($query) use ($request) {
            $query->leftJoin('tblm_region', 'tblm_country.id', '=', 'tblm_region.link_country_id')
            ->leftJoin('tblm_state', 'tblm_country.id', '=', 'tblm_state.link_country_id')
            ->leftJoin('tblm_towncity', 'tblm_state.id', '=', 'tblm_towncity.link_state_id')
            ->leftJoin('tblm_barangay', 'tblm_towncity.id', '=', 'tblm_barangay.link_towncity_id')
            ->where('tblm_region.description', 'LIKE', "{$request->search}%")
            ->orWhere('tblm_state.description', 'LIKE', "{$request->search}%")
            ->orWhere('tblm_towncity.description', 'LIKE', "{$request->search}%")
            ->orWhere('tblm_barangay.description', 'LIKE', "{$$request->search}%")
            ->orWhere('tblm_country.short_desc', 'LIKE', "{$request->search}%")
            ->orWhere('tblm_country.long_desc', 'LIKE', "{$request->search}%");
        })
        ->distinct()
        ->orderBy('tblm_country.id', 'desc')
        ->paginate($request->limit);

        if( $countries->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $countries,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $countries = Country::
        when($request->search, function ($query) use ($request) {
            $query->where('short_desc', 'LIKE', "{$request->search}%")
                ->orWhere('long_desc', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : Country::count());

        if( $countries->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $countries,
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
            'short_desc' => 'required|max:5|unique:tblm_country,short_desc|regex:/^[A-Za-z0-9,\- ]+$/',
            'long_desc' => 'required|max:30|unique:tblm_country,long_desc|regex:/^[A-Za-z0-9,\- ]+$/',
            'with_region' => 'required|boolean'
        ],
        [
            'short_desc.required' => 'The Country Code is requried.',
            'short_desc.regex' => 'The Country Code should not contain any special characters.',
            'long_desc.required' => 'The Country Name is requried.',
            'long_desc.regex' => 'The Country Name should not contain any special characters.',
            'short_desc.unique' => 'The Country Code already exist.',
            'long_desc.unique' => 'The Country Name already exist.',
            'short_desc.max' => 'The Country Code must not exceed 5 characters.',
            'long_desc.max' => 'The Country Name must not exceed 30 characters.'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $countries = Country::create([
            'short_desc' => $request->short_desc,
            'long_desc' => $request->long_desc,
            'with_region' => $request->with_region,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $countries,
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
        $countries = Country::
        with(['regions' => function ($query) {
            $query->with(['states']);
        }])
        ->with(['states'])
        ->with(['currency'])
        ->where('id', $id)
        ->first();

        if( !$countries) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $countries,
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
        $countries = Country::find($id);

        if( !$countries ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'short_desc' => [
                Rule::prohibitedIf(Country::where('short_desc', $request->short_desc)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:5',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ],
            'long_desc' => [
                Rule::prohibitedIf(Country::where('long_desc', $request->short_desc)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:30',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ],
            'with_region' => 'required|boolean'
        ],
        [
            'short_desc.required' => 'The Country Code is requried.',
            'short_desc.regex' => 'The Country Code should not contain any special characters.',
            'long_desc.required' => 'The Country Name is requried.',
            'long_desc.regex' => 'The Country Name should not contain any special characters.',
            'short_desc.prohibited' => 'The Country Code already exist.',
            'long_desc.prohibited' => 'The Country Name already exist.',
            'short_desc.max' => 'The Country Code must not exceed 5 characters.',
            'long_desc.max' => 'The Country Name must not exceed 30 characters.'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Country::where('id', $id)->update([
            'short_desc' => $request->short_desc,
            'long_desc' => $request->long_desc,
            'with_region' => $request->with_region,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => Country::find($id),
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
        $countries = Country::find($id);

        if( !$countries ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $countries->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
