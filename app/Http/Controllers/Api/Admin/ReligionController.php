<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Religion;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReligionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $religions = Religion::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : Religion::count());

        if( $religions->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $religions,
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
            'description' => 'required|max:30|unique:tblm_religion,description|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'description.required' => 'The Religion is required.',
            'description.regex' => 'The Religion should not contain any special characters.',
            'description.unique' => 'The Religion already exists.',
            'description.max' => 'The Religion must not exceed 30 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $religions = Religion::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $religions,
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
        $religions = Religion::
        where('id', $id)
        ->first();

        if( !$religions) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $religions,
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
        $religions = Religion::find($id);

        if( !$religions ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(Religion::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:30',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'description.required' => 'The Religion is required.',
            'description.regex' => 'The Religion should not contain any special characters.',
            'description.prohibited' => 'The Religion already exists.',
            'description.max' => 'The Religion must not exceed 30 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Religion::where('id', $id)->update([
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => Religion::find($id)
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
        $religions = Religion::find($id);

        if( !$religions ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $religions->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
