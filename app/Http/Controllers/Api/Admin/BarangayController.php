<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Barangay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class BarangayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $barangays = Barangay::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->with(['towncity' => function ($query) {
            $query->with(['state' => function ($query) {
                $query->with(['country'])
                ->with(['region' => function ($query) {
                    $query->with(['country']);
                }]);
            }]);
        }])
        ->orWhereHas('towncity', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('description', 'LIKE', "{$request->search}%")
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
                });;
            });
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : Barangay::count());

        if( $barangays->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $barangays,
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
            'towncity_id' => [
                'required',
                'exists:tblm_towncity,id',
                Rule::prohibitedIf(Barangay::where('link_towncity_id', $request->towncity_id)
                                ->where('description', $request->description)
                                ->exists())
            ],
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/',
        ],
        [
            'towncity_id.required' => 'The Town/City is required.',
            'towncity_id.exists' => 'The selected Town/City is invalid.',
            'towncity_id.prohibited' => 'The selected Town/City and Baranggay/Village already exists.',
            'description.required' => 'The Barangay is required.',
            'description.regex' => 'The Barangay should not contain any special characters.',
            'description.max' => 'The Barangay must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $barangays = Barangay::create([
            'link_towncity_id' => $request->towncity_id,
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $barangays,
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
        $barangays = Barangay::
        with(['towncity' => function ($query) {
            $query->with(['state' => function ($query) {
                $query->with(['country'])
                ->with(['region' => function ($query) {
                    $query->with(['country']);
                }]);
            }]);
        }])
        ->where('id', $id)
        ->first();

        if( !$barangays ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $barangays,
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
        $barangays = Barangay::find($id);

        if( !$barangays ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'towncity_id' => [
                'required',
                'exists:tblm_towncity,id',
                Rule::prohibitedIf(Barangay::where('link_towncity_id', $request->towncity_id)
                                ->where('description', $request->description)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/',
        ],
        [
            'towncity_id.required' => 'The Town/City is required.',
            'towncity_id.exists' => 'The selected Town/City is invalid.',
            'towncity_id.prohibited' => 'The selected Town/City and Baranggay/Village already exists.',
            'description.required' => 'The Barangay is required.',
            'description.regex' => 'The Barangay should not contain any special characters.',
            'description.max' => 'The Barangay must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Barangay::where('id', $id)->update([
            'link_towncity_id' => $request->towncity_id,
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => Barangay::find($id)
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
        $barangays = Barangay::find($id);

        if( !$barangays ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $barangays->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
