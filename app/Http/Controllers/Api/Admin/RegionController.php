<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $regions = Region::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->with(['country'])
        ->orWhereHas('country', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('short_desc', 'LIKE', "{$request->search}%");
                $query->orWhere('long_desc', 'LIKE', "{$request->search}%");
            });
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : Region::count());

        if( $regions->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $regions,
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
            'country_id' => 'required|exists:tblm_country,id',
            'description' => 'required|max:20|unique:tblm_region,description|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'country_id.required' => 'The Country is required.',
            'country_id.exists' => 'The selected Country is invalid.',
            'description.unique' => 'The Region already exists.',
            'description.required' => 'The Region is required.',
            'description.regex' => 'The Region should not contain any special characters.',
            'description.max' => 'The Region must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $regions = Region::create([
            'link_country_id' => $request->country_id,
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $regions,
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
        $regions = Region::
        with(['states'])
        ->with(['country'])
        ->where('id', $id)
        ->first();

        if( !$regions) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $regions,
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
        $regions = Region::find($id);

        if( !$regions ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:tblm_country,id',
            'description' => [
                Rule::prohibitedIf(Region::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:20',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'country_id.required' => 'The Country is required.',
            'country_id.exists' => 'The selected Country is invalid.',
            'description.prohibited' => 'The Region already exists.',
            'description.required' => 'The Region is required.',
            'description.regex' => 'The Region should not contain any special characters.',
            'description.max' => 'The Region must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Region::where('id', $id)->update([
            'link_country_id' => $request->country_id,
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => Region::find($id)
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
        $regions = Region::find($id);

        if( !$regions ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $regions->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
