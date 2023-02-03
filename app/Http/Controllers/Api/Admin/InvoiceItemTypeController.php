<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\InvoiceItemType;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class InvoiceItemTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $item_invoice_type = InvoiceItemType::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : InvoiceItemType::count());

        if( $item_invoice_type->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $item_invoice_type,
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
            'description' => 'required|max:50|unique:tblm_invoice_item_type,description|regex:/^[A-Za-z0-9,\- ]+$/',
            'percentage' => 'required_if:is_percentage,true|numeric|nullable'
        ],
        [
            'description.required' => 'The Invoice Item Type is required.',
            'description.regex' => 'The Invoice Item Type should not contain any special characters.',
            'description.unique' => 'The Invoice Item Type already exists.',
            'description.max' => 'The Invoice Item Type must not exceed 50 characters.',
            'percentage.required_if' => 'The Percentage is required.',
            'percentage.numeric' => 'The Percentage should only contain numbers.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $item_invoice_type = InvoiceItemType::create([
            'description' => $request->description,
            'is_percentage' => $request->is_percentage,
            'percentage' => $request->percentage,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $item_invoice_type,
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
        $item_invoice_type = InvoiceItemType::
        where('id', $id)
        ->first();

        if( !$item_invoice_type) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $item_invoice_type,
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
        $item_invoice_type = InvoiceItemType::find($id);

        if( !$item_invoice_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(InvoiceItemType::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:50',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ],
            'percentage' => 'required_if:is_percentage,true|numeric|nullable'
        ],
        [
            'description.required' => 'The Invoice Item Type is required.',
            'description.regex' => 'The Invoice Item Type should not contain any special characters.',
            'description.prohibited' => 'The Invoice Item Type already exists.',
            'description.max' => 'The Invoice Item Type must not exceed 10 characters.',
            'percentage.required_if' => 'The Percentage is required.',
            'percentage.numeric' => 'The Percentage should only contain numbers.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        InvoiceItemType::where('id', $id)->update([
            'description' => $request->description,
            'is_percentage' => $request->is_percentage,
            'percentage' => $request->percentage,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => InvoiceItemType::find($id)
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
        $item_invoice_type = InvoiceItemType::find($id);

        if( !$item_invoice_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $item_invoice_type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
