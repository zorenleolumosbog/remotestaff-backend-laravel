<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Admin\CancellationReasonType;

class CancellationReasonTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $cancellation_reasons = CancellationReasonType::when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->with(['contract_status'])
        ->orWhereHas('contract_status', function (Builder $query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : CancellationReasonType::count());

        if( $cancellation_reasons->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $cancellation_reasons,
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
            'contract_status_id' => [
                'required',
                'exists:tblm_contract_status,id',
                Rule::prohibitedIf(CancellationReasonType::where('link_contract_status_id', $request->contract_status_id)
                                ->where('description', $request->description)
                                ->exists())
            ],
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'contract_status_id.required' => 'The Contract Status is required.',
            'contract_status_id.exists' => 'The selected Contract Status is invalid.',
            'contract_status_id.prohibited' => 'The selected Contract Status and Cancellation Reason description already exists.',
            'description.required' => 'The Cancellation Reason is required.',
            'description.regex' => 'The Cancellation Reason should not contain any special characters.',
            'description.max' => 'The Cancellation Reason must not exceed 20 characters.',
        ]);

		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $cancellation_reason = CancellationReasonType::create([
            'link_contract_status_id' => $request->contract_status_id,
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => now()
        ]);

		return response()->json([
            'success' => true,
            'message' => 'Successfully added.',
            'data' => $cancellation_reason
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
        $cancellation_reason = CancellationReasonType::where('id', $id)->with('contract_status')->first();

        if( !$cancellation_reason ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $cancellation_reason
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
        $cancellation_reason = CancellationReasonType::find($id);

        if( !$cancellation_reason ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

		$validator = Validator::make($request->all(), [
            'contract_status_id' => [
                'required',
                'exists:tblm_contract_status,id',
                Rule::prohibitedIf(CancellationReasonType::where('link_contract_status_id', $request->contract_status_id)
                                ->where('description', $request->description)
                                ->exists())
            ],
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'contract_status_id.required' => 'The Contract Status is required.',
            'contract_status_id.exists' => 'The selected Contract Status is invalid.',
            'contract_status_id.prohibited' => 'The selected Contract Status and Cancellation Reason description already exists.',
            'description.required' => 'The Cancellation Reason is required.',
            'description.regex' => 'The Cancellation Reason should not contain any special characters.',
            'description.max' => 'The Cancellation Reason must not exceed 20 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $cancellation_reason->link_contract_status_id = $request->contract_status_id;
        $cancellation_reason->description = $request->description;
        $cancellation_reason->modifiedby = auth()->user()->id;
        $cancellation_reason->datemodified = now();
        $cancellation_reason->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => $cancellation_reason
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
        $cancellation_reason = CancellationReasonType::find($id);

        if( !$cancellation_reason ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $cancellation_reason->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
