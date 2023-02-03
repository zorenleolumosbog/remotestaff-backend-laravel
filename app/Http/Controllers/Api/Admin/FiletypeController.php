<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\FileType;

class FileTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $file_types = FileType::when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : FileType::count());

        if( $file_types->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $file_types,
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
            'description' => 'required|max:30|unique:tblm_filetype,description|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'description.required' => 'The File type is required.',
            'description.regex' => 'The File type should not contain any special characters.',
            'description.unique' => 'The File type already exists.',
            'description.max' => 'The File type must not exceed 30 characters.',
        ]);

		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $file_type = FileType::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => now()
        ]);

		return response()->json([
            'success' => true,
            'message' => 'Successfully added.',
            'data' => $file_type
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
        $file_type = FileType::where('id', $id)->first();

        if( !$file_type ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $file_type
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
        $file_type = FileType::find($id);

        if( !$file_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(FileType::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:30',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'description.required' => 'The File type is required.',
            'description.regex' => 'The File type should not contain any special characters.',
            'description.prohibited' => 'The File type already exists.',
            'description.max' => 'The File type must not exceed 30 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $file_type->description = $request->description;
        $file_type->modifiedby = auth()->user()->id;
        $file_type->datemodified = now();
        $file_type->save();

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => $file_type
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
        $file_type = FileType::find($id);

        if( !$file_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $file_type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
