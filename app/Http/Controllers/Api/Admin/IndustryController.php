<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Industry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IndustryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $industry = Industry::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : Industry::count());

        if( $industry->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $industry,
        ], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'description' => 'required|max:30|unique:tblm_industry,description|regex:/^[A-Za-z0-9,\- ]+$/',
        ],
        [
            'description.required' => 'Description is requried.',
            'description.regex' => 'The Description should not contain any special characters.',
            'description.max' => 'Description must not exceed 30 characters.',
            'description.unique' => 'This record has already been added.'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $industry = Industry::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $industry,
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
        $industry = Industry::
        where('id', $id)
        ->first();

        if( !$industry) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $industry,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Admin\Industry  $industry
     * @return \Illuminate\Http\Response
     */
    public function edit(Industry $industry)
    {
        //
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
        $industry = Industry::find($id);

        if( !$industry ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(Industry::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:30',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'description.required' => 'Description is requried.',
            'description.regex' => 'The Description should not contain any special characters.',
            'description.max' => 'Description must not exceed 30 characters.',
            'description.unique' => 'This record has already been added.'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Industry::where('id', $id)->update([
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => Industry::find($id),
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin\Industry  $industry
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $industry = Industry::find($id);

        if( !$industry ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $industry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
