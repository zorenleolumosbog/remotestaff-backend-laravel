<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\ContractStatus;

class ContractStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $contract_statuses = ContractStatus::when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : ContractStatus::count());

        if( $contract_statuses->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $contract_statuses,
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
            'description' => 'required|max:20|unique:tblm_contract_status,description|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'description.required' => 'The Contract Status is required.',
            'description.regex' => 'The Contract Status should not contain any special characters.',
            'description.unique' => 'The Contract Status already exists.',
            'description.max' => 'The Contract Status must not exceed 20 characters.',
        ]);

		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $contract_status = ContractStatus::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => now()
        ]);

		return response()->json([
            'success' => true,
            'message' => 'Successfully added.',
            'data' => $contract_status
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
        $contract_status = ContractStatus::where('id', $id)->first();

        if( !$contract_status ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $contract_status
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
        $contract_status = ContractStatus::find($id);

        if( !$contract_status ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(ContractStatus::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:20',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'description.required' => 'The Contract Status is required.',
            'description.regex' => 'The Contract Status should not contain any special characters.',
            'description.prohibited' => 'The Contract Status already exists.',
            'description.max' => 'The Contract Status must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $contract_status->description = $request->description;
        $contract_status->modifiedby = auth()->user()->id;
        $contract_status->datemodified = now();
        $contract_status->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => $contract_status
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
        $contract_status = ContractStatus::find($id);

        if( !$contract_status ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $contract_status->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
