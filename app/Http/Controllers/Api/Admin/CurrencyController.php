<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //TODO: Search for country
        $currencies = Currency::
        when($request->search, function ($query) use ($request) {
            $query->where('code', 'LIKE', "{$request->search}%");
            $query->orWhere('symbol', 'LIKE', "{$request->search}%");
            $query->orWhere('description', 'LIKE', "{$request->search}%");
            $query->orWhere('rate', 'LIKE', "{$request->search}%");
        })
        ->with(['country'])
        ->orWhereHas('country', function (Builder $query) use ($request) {
            $query->where('short_desc', 'LIKE', "{$request->search}%");
            $query->orWhere('long_desc', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : Currency::count());

        if( $currencies->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $currencies,
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
            'country_id' => 'required|unique:tblm_currency,link_country_id|exists:tblm_country,id',
            'code' => 'required|max:5|unique:tblm_currency,code|regex:/^[A-Za-z0-9,\- ]+$/',
            'symbol' => 'required|max:5',
            'description' => 'required|max:40|unique:tblm_currency,description|regex:/^[A-Za-z0-9,\- ]+$/',
            'rate' => 'required|numeric'
        ],
        [
            'country_id.required' => 'The Country is required.',
            'country_id.exists' => 'The selected Country is invalid.',
            'country_id.unique' => 'The Country already exists.',
            'code.required' => 'The Currency Code is required.',
            'code.regex' => 'The Currency Code should not contain any special characters.',
            'code.max' => 'The Currency Code must not be greater than 5 characters.',
            'code.unique' => 'The Currency Code already exists.',
            'symbol.required' => 'The Currency Symbol is required.',
            'symbol.max' => 'The Currency Symbol must not be greater than 5 characters.',
            'description.unique' => 'The description already exists.',
            'description.regex' => 'The description should not contain any special characters.',
            'rate.required' => 'The Currency Rate is required.',
            'rate.numeric' => 'The Currency Rate must be a number.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $currencies = Currency::create([
            'link_country_id' => $request->country_id,
            'code' => $request->code,
            'symbol' => $request->symbol,
            'description' => $request->description,
            'rate' => $request->rate,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $currencies,
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
        $currencies = Currency::
        with(['country'])
        ->where('id', $id)
        ->first();

        if( !$currencies) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $currencies,
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
        $currencies = Currency::find($id);

        if( !$currencies ) {
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
                Rule::prohibitedIf(Currency::where('link_country_id', $request->country_id)
                ->where('id', '!=', $id)->exists()),
            ],
            'code' => [
                Rule::prohibitedIf(Currency::where('code', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:5',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ],
            'symbol' => 'required|max:5',
            'description' => [
                Rule::prohibitedIf(Currency::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:40',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ],
            'rate' => 'required|numeric'
        ],
        [
            'country_id.required' => 'The Country is required.',
            'country_id.exists' => 'The selected Country is invalid.',
            'country_id.prohibited' => 'The Country already exists.',
            'code.required' => 'The Currency Code is required.',
            'code.regex' => 'The Currency Code should not contain any special characters.',
            'code.max' => 'The Currency Code must not be greater than 5 characters.',
            'code.prohibited' => 'The Currency Code already exists.',
            'symbol.required' => 'The Currency Symbol is required.',
            'symbol.max' => 'The Currency Symbol must not be greater than 5 characters.',
            'description.prohibited' => 'The description already exists.',
            'description.regex' => 'The description should not contain any special characters.',
            'rate.required' => 'The Currency Rate is required.',
            'rate.numeric' => 'The Currency Rate must be a number.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Currency::where('id', $id)->update([
            'link_country_id' => $request->country_id,
            'code' => $request->code,
            'symbol' => $request->symbol,
            'description' => $request->description,
            'rate' => $request->rate,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => Currency::find($id)
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
        $currencies = Currency::find($id);

        if( !$currencies ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $currencies->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
