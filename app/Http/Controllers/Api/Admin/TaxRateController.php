<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\TaxRate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tax_rates = TaxRate::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
            $query->orWhere('rate', 'LIKE', "{$request->search}%");
        })
        ->with(['country', 'taxType'])
        ->with('state', function($query) {
            $query->with('region', function($query) {
                $query->with(['country']);
            })
            ->with(['country']);
        })
        ->orWhereHas('country', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('short_desc', 'LIKE', "{$request->search}%");
                $query->orWhere('long_desc', 'LIKE', "{$request->search}%");
            });
        })
        ->orWhereHas('state', function (Builder $query) use ($request) {
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
        ->orWhereHas('taxType', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('short_desc', 'LIKE', "{$request->search}%");
                $query->orWhere('long_desc', 'LIKE', "{$request->search}%");
            });
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : TaxRate::count());

        if( $tax_rates->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $tax_rates,
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
                'required',
                'exists:tblm_country,id',
                'required_if:state_applied,false',
                Rule::prohibitedIf(TaxRate::where('link_country_id', $request->country_id)
                                ->where('link_tax_type_id', $request->tax_type_id)
                                ->exists())
            ],
            'state_id' => [
                'nullable',
                'exists:tblm_state,id',
                'required_if:state_applied,true',
                Rule::prohibitedIf(TaxRate::where('link_state_id', $request->state_id)
                                ->where('link_tax_type_id', $request->tax_type_id)
                                ->exists())
            ],
            'tax_type_id' => 'required|exists:tblm_tax_type,id',
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/',
            'state_applied' => 'required|boolean',
            'rate' => 'required|numeric'
        ],
        [
            'country_id.required' => 'The Country is required.',
            'country_id.exists' => 'The selected Country is invalid.',
            'country_id.required_if' => 'The Country is required.',
            'country_id.prohibited' => 'The selected Country and Tax Type already exists.',
            'state_id.required' => 'The State is required.',
            'state_id.exists' => 'The selected State is invalid.',
            'state_id.required_if' => 'The State is required.',
            'state_id.prohibited' => 'The selected State and Tax Type already exists.',
            'tax_type_id.required' => 'The Tax Type is required.',
            'tax_type_id.exists' => 'The selected Tax Type is invalid.',
            'description.required' => 'The Tax Rate is required.',
            'description.regex' => 'The Tax Rate should not contain any special characters.',
            'description.max' => 'The Tax Rate must not exceed 50 characters.',
            'rate.required' => 'The Tax Rate is required.',
            'rate.numeric' => 'The Tax Rate must be a number.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $tax_rates = TaxRate::create([
            'link_country_id' => $request->state_applied ? null : $request->country_id,
            'link_state_id' => $request->state_applied ? $request->state_id : null,
            'link_tax_type_id' => $request->tax_type_id,
            'state_applied' => $request->state_applied,
            'description' => $request->description,
            'rate' => $request->rate,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $tax_rates,
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
        $tax_rates = TaxRate::
        with(['country', 'taxType'])
        ->with('state', function($query) {
            $query->with('region', function($query) {
                $query->with(['country']);
            })
            ->with(['country']);
        })
        ->where('id', $id)
        ->first();

        if( !$tax_rates) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $tax_rates,
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
        $tax_rates = TaxRate::find($id);

        if( !$tax_rates ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'country_id' => [
                'required',
                'exists:tblm_country,id',
                'required_if:state_applied,false',
                Rule::prohibitedIf(TaxRate::where('link_country_id', $request->country_id)
                                ->where('link_tax_type_id', $request->tax_type_id)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'state_id' => [
                'nullable',
                'exists:tblm_state,id',
                'required_if:state_applied,true',
                Rule::prohibitedIf(TaxRate::where('link_state_id', $request->state_id)
                                ->where('link_tax_type_id', $request->tax_type_id)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'tax_type_id' => 'required|exists:tblm_tax_type,id',
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/',
            'state_applied' => 'required|boolean',
            'rate' => 'required|numeric'
        ],
        [
            'country_id.required' => 'The Country is required.',
            'country_id.exists' => 'The selected Country is invalid.',
            'country_id.required_if' => 'The Country is required.',
            'country_id.prohibited' => 'The selected Country and Tax Type already exists.',
            'state_id.required' => 'The State is required.',
            'state_id.exists' => 'The selected State is invalid.',
            'state_id.required_if' => 'The State is required.',
            'state_id.prohibited' => 'The selected State and Tax Type already exists.',
            'tax_type_id.required' => 'The Tax Type is required.',
            'tax_type_id.exists' => 'The selected Tax Type is invalid.',
            'description.required' => 'The Tax Rate is required.',
            'description.regex' => 'The Tax Rate should not contain any special characters.',
            'description.max' => 'The Tax Rate must not exceed 50 characters.',
            'rate.required' => 'The Tax Rate is required.',
            'rate.numeric' => 'The Tax Rate must be a number.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        TaxRate::where('id', $id)->update([
            'link_country_id' => $request->state_applied ? null : $request->country_id,
            'link_state_id' => $request->state_applied ? $request->state_id : null,
            'link_tax_type_id' => $request->tax_type_id,
            'state_applied' => $request->state_applied,
            'description' => $request->description,
            'rate' => $request->rate,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => TaxRate::find($id),
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
        $tax_rates = TaxRate::find($id);

        if( !$tax_rates ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $tax_rates->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
