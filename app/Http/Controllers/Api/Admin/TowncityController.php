<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Towncity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class TowncityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $towncities = Towncity::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->with(['state' => function ($query) {
            $query->with(['country'])
            ->with(['region' => function ($query) {
                $query->with(['country']);
            }]);
        }])
        ->orWhereHas('state', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('description', 'LIKE', "{$request->search}%")
                ->orWhereHas('country', function (Builder $query) use ($request) {
                    $query->when($request->search, function ($query) use ($request) {
                        $query->where('short_desc', 'LIKE', "{$request->search}%");
                        $query->orWhere('long_desc', 'LIKE', "{$request->search}%");
                    });
                });
            })
            ->orWhereHas('region', function (Builder $query) use ($request) {
                $query->where('description', 'LIKE', "{$request->search}%")
                ->orWhereHas('country', function (Builder $query) use ($request) {
                    $query->when($request->search, function ($query) use ($request) {
                        $query->where('short_desc', 'LIKE', "{$request->search}%");
                        $query->orWhere('long_desc', 'LIKE', "{$request->search}%");
                    });
                });
            });
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : Towncity::count());

        if( $towncities->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $towncities,
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
            'state_id' => [
                'required',
                'exists:tblm_state,id',
                Rule::prohibitedIf(Towncity::where('link_state_id', $request->state_id)
                                ->where('zip_code', $request->zip_code)
                                ->where('description', $request->description)
                                ->exists())
            ],
            'zip_code' => 'required|integer',
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'state_id.required' => 'The State is required.',
            'state_id.exists' => 'The selected State is invalid.',
            'state_id.prohibited' => 'The selected State, Zip Code and Town/City already exists.',
            'zip_code.required' => 'The Zip Code is required.',
            'zip_code.integer' => 'The Zip Code must be integer.',
            'description.required' => 'The Towncity is required.',
            'description.regex' => 'The Towncity should not contain any special characters.',
            'description.max' => 'The Towncity must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $towncities = Towncity::create([
            'link_state_id' => $request->state_id,
            'zip_code' => $request->zip_code,
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $towncities,
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
        $towncities = Towncity::
        with(['barangays'])
        ->with(['state' => function ($query) {
            $query->with(['country'])
            ->with(['region' => function ($query) {
                $query->with(['country']);
            }]);
        }])
        ->where('id', $id)
        ->first();

        if( !$towncities) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $towncities,
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
        $towncities = Towncity::find($id);

        if( !$towncities ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'state_id' => [
                'required',
                'exists:tblm_state,id',
                Rule::prohibitedIf(Towncity::where('link_state_id', $request->state_id)
                                ->where('zip_code', $request->zip_code)
                                ->where('description', $request->description)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'zip_code' => 'required|integer',
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'state_id.required' => 'The State is required.',
            'state_id.exists' => 'The selected State is invalid.',
            'state_id.prohibited' => 'The selected State, Zip Code and Town/City already exists.',
            'zip_code.required' => 'The Zip Code is required.',
            'zip_code.integer' => 'The Zip Code must be integer.',
            'description.required' => 'The Towncity is required.',
            'description.regex' => 'The Towncity should not contain any special characters.',
            'description.max' => 'The Towncity must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Towncity::where('id', $id)->update([
            'link_state_id' => $request->state_id,
            'zip_code' => $request->zip_code,
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => Towncity::find($id)
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
        $towncities = Towncity::find($id);

        if( !$towncities ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $towncities->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
