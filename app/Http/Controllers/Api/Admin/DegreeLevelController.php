<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\DegreeLevel;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DegreeLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $degree_levels = DegreeLevel::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : DegreeLevel::count());

        if( $degree_levels->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $degree_levels,
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
            'description' => 'required|max:50|unique:tblm_degree_level,description|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'description.required' => 'The Degree Level is required.',
            'description.regex' => 'The Degree Level should not contain any special characters.',
            'description.unique' => 'The Degree Level already exists.',
            'description.max' => 'The Degree Level must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $degree_levels = DegreeLevel::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $degree_levels,
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
        $degree_levels = DegreeLevel::
        where('id', $id)
        ->first();

        if( !$degree_levels) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $degree_levels,
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
        $degree_levels = DegreeLevel::find($id);

        if( !$degree_levels ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(DegreeLevel::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:50',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'description.required' => 'The Degree Level is required.',
            'description.regex' => 'The Degree Level should not contain any special characters.',
            'description.prohibited' => 'The Degree Level already exists.',
            'description.max' => 'The Degree Level must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        DegreeLevel::where('id', $id)->update([
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => DegreeLevel::find($id)
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
        $degree_levels = DegreeLevel::find($id);

        if( !$degree_levels ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $degree_levels->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
