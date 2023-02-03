<?php

namespace App\Http\Controllers\Api\SecurityAccessMatrix;

use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

use App\Models\SecurityAccessMatrix\Pillar;

class PillarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $result = Pillar::where('isactive', 1)->get()->paginate(intval($request->limit));
        return response()->json(['success' => true, 'message' => 'Show list of pillars', 'data' => $result ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // return response()->json(['success' => true, 'message' => 'Create user'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        
        // Set validation
		$validator = Validator::make($request->all(), [
            'description' => 'required'
        ],
        [
            'description.required' => 'The Pillar name is requried.'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $data = [
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ];

        $result = Pillar::create($data);

        return response()->json([
            'success' => true,
            'data' => $result,
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
        $result = Pillar::find($id);
        $result->sub_pillars;
        return response()->json(['success' => true, 'message' => "Display pillar {$id}", 'data' => $result], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        // Set validation
		$validator = Validator::make($request->all(), [
            'description' => 'required'
        ],
        [
            'description.required' => 'The Pillar name is requried.'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Pillar::find($id)->update([
            'description'=>$request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);
        return response()->json(['success' => true, 'message' => "Updated Pillar {$id}"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Pillar::find($id)->update([
            'isactive'=>0,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);
        return response()->json(['success' => true, 'message' => "Deleted Pillar {$id}"], 200);
    }
}