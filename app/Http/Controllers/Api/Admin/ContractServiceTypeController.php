<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\ContractServiceType;

class ContractServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $contract_service_types = ContractServiceType::when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : ContractServiceType::count());

        if( $contract_service_types->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $contract_service_types,
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
		$validator = Validator::make($request->all(), [
            'description' => 'required|max:20|unique:tblm_contract_service_types,description|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'description.required' => 'The Contract Service Type is required.',
            'description.regex' => 'The Contract Service Type should not contain any special characters.',
            'description.unique' => 'The Contract Service Type already exists.',
            'description.max' => 'The Contract Service Type must not exceed 20 characters.',
        ]);

		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $contract_service_type = ContractServiceType::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => now()
        ]);

		return response()->json([
            'success' => true,
            'message' => 'Successfully added.',
            'data' => $contract_service_type
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
        $contract_service_type = ContractServiceType::where('id', $id)->first();

        if( !$contract_service_type ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $contract_service_type
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $contract_service_type = ContractServiceType::find($id);

        if( !$contract_service_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(ContractServiceType::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:20',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'description.required' => 'The Contract Service Type is required.',
            'description.regex' => 'The Contract Service Type should not contain any special characters.',
            'description.prohibited' => 'The Contract Service Type already exists.',
            'description.max' => 'The Contract Service Type must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $contract_service_type->description = $request->description;
        $contract_service_type->modifiedby = auth()->user()->id;
        $contract_service_type->datemodified = now();
        $contract_service_type->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => $contract_service_type
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
        $contract_service_type = ContractServiceType::find($id);

        if( !$contract_service_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $contract_service_type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
