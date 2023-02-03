<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ForexRateType;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForexRateTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $forex_rate_types = ForexRateType::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : ForexRateType::count());

        if( $forex_rate_types->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $forex_rate_types,
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
            'description' => 'required|max:20|unique:tblm_forex_rate_type,description'
        ],
        [
            'description.required' => 'The Forex Rate Type description is required.',
            'description.unique' => 'The Forex Rate Type description already exists.',
            'description.max' => 'The Forex Rate Type description must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $forex_rate_types = ForexRateType::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $forex_rate_types,
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
        $forex_rate_types = ForexRateType::
        where('id', $id)
        ->first();

        if( !$forex_rate_types) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $forex_rate_types,
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
        $forex_rate_types = ForexRateType::find($id);
        
        if( !$forex_rate_types ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(ForexRateType::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:20'
            ]
        ],
        [
            'description.required' => 'The Forex Rate Type description is required.',
            'description.prohibited' => 'The Forex Rate Type description already exists.',
            'description.max' => 'The Forex Rate Type description must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        ForexRateType::where('id', $id)->update([
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => ForexRateType::find($id)
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
        $forex_rate_types = ForexRateType::find($id);
        
        if( !$forex_rate_types ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $forex_rate_types->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
