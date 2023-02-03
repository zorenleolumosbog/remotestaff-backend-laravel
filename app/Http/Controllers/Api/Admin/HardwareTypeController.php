<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\HardwareType;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HardwareTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $hardware_types = HardwareType::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : HardwareType::count());

        if( $hardware_types->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $hardware_types,
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
            'description' => 'required|max:50|unique:tblm_comp_hardwaretype,description|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'description.required' => 'The Hardware Type is required.',
            'description.regex' => 'The Hardware Type should not contain any special characters.',
            'description.unique' => 'The Hardware Type already exists.',
            'description.max' => 'The Hardware Type must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $hardware_types = HardwareType::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $hardware_types,
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
        $hardware_types = HardwareType::
        where('id', $id)
        ->first();

        if( !$hardware_types) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $hardware_types,
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
        $hardware_types = HardwareType::find($id);

        if( !$hardware_types ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(HardwareType::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:50',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'description.required' => 'The Hardware Type is required.',
            'description.regex' => 'The Hardware Type should not contain any special characters.',
            'description.prohibited' => 'The Hardware Type already exists.',
            'description.max' => 'The Hardware Type must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        HardwareType::where('id', $id)->update([
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => HardwareType::find($id)
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
        $hardware_types = HardwareType::find($id);

        if( !$hardware_types ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $hardware_types->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
