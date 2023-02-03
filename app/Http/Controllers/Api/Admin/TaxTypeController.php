<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\TaxType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaxTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tax_types = TaxType::
        when($request->search, function ($query) use ($request) {
            $query->where('short_desc', 'LIKE', "{$request->search}%")
                ->orWhere('long_desc', 'LIKE', "{$request->search}%");
        })
        ->with(['taxRates'])
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : TaxType::count());

        if( $tax_types->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $tax_types,
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
            'short_desc' => 'required|max:10|unique:tblm_tax_type,short_desc|regex:/^[A-Za-z0-9,\- ]+$/',
            'long_desc' => 'required|max:50|unique:tblm_tax_type,long_desc|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'short_desc.required' => 'The Tax Type Code is requried.',
            'short_desc.regex' => 'The Tax Type Code should not contain any special characters.',
            'short_desc.unique' => 'The Tax Type Code already exist.',
            'short_desc.max' => 'The Tax Type Code must not exceed to 10 characters.',
            'long_desc.required' => 'The Tax Type is requried.',
            'long_desc.regex' => 'The Tax Type should not contain any special characters.',
            'long_desc.unique' => 'The Tax Type already exist.',
            'long_desc.max' => 'The Tax Type must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $tax_types = TaxType::create([
            'short_desc' => $request->short_desc,
            'long_desc' => $request->long_desc,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $tax_types,
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
        $tax_types = TaxType::
        where('id', $id)
        ->first();

        if( !$tax_types) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $tax_types,
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
        $tax_types = TaxType::find($id);

        if( !$tax_types ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'short_desc' => [
                Rule::prohibitedIf(TaxType::where('short_desc', $request->short_desc)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:10',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ],
            'long_desc' => [
                Rule::prohibitedIf(TaxType::where('long_desc', $request->short_desc)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:50',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'short_desc.required' => 'The Tax Type Code is requried.',
            'short_desc.regex' => 'The Tax Type Code should not contain any special characters.',
            'short_desc.prohibited' => 'The Tax Type Code already exist.',
            'short_desc.max' => 'The Tax Type Code must not exceed to 10 characters.',
            'long_desc.required' => 'The Tax Type is requried.',
            'long_desc.regex' => 'The Tax Type should not contain any special characters.',
            'long_desc.prohibited' => 'The Tax Type already exist.',
            'long_desc.max' => 'The Tax Type must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        TaxType::where('id', $id)->update([
            'short_desc' => $request->short_desc,
            'long_desc' => $request->long_desc,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => TaxType::find($id),
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
        $tax_types = TaxType::find($id);

        if( !$tax_types ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $tax_types->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
