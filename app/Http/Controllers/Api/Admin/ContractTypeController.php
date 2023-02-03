<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\ContractType;

class ContractTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $contract_typees = ContractType::when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : ContractType::count());

        if( $contract_typees->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $contract_typees,
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
            'description' => 'required|max:20|unique:tblm_contract_type,description'
        ],
        [
            'description.required' => 'The Contract Type description is required.',
            'description.unique' => 'The Contract Type description already exists.',
            'description.max' => 'The Contract Type description must not exceed 20 characters.',
        ]);

		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $contract_type = ContractType::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => now()
        ]);

		return response()->json([
            'success' => true,
            'message' => 'Successfully added.',
            'data' => $contract_type
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
        $contract_type = ContractType::where('id', $id)->first();

        if( !$contract_type ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $contract_type
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
        $contract_type = ContractType::find($id);
        
        if( !$contract_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}
        
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(ContractType::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:20'
            ]
        ],
        [
            'description.required' => 'The Contract Type description is required.',
            'description.prohibited' => 'The Contract Type description already exists.',
            'description.max' => 'The Contract Type description must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $contract_type->description = $request->description;
        $contract_type->modifiedby = auth()->user()->id;
        $contract_type->datemodified = now();
        $contract_type->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => $contract_type
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
        $contract_type = ContractType::find($id);
        
        if( !$contract_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $contract_type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
