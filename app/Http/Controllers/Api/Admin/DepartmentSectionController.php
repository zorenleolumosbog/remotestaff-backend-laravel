<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\DepartmentSection;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class DepartmentSectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $department_sections = DepartmentSection::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->with(['personnel', 'department'])
        ->orWhereHas('department', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('description', 'LIKE', "{$request->search}%");
            });
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : DepartmentSection::count());

        if( $department_sections->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $department_sections,
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
            'department_id' => [
                'required',
                'exists:tblo_dept,id',
                Rule::prohibitedIf(DepartmentSection::where('link_dept_id', $request->department_id)
                                ->where('description', $request->description)
                                ->exists())
            ],
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'department_id.required' => 'The Department is required.',
            'department_id.exists' => 'The selected Department is invalid.',
            'department_id.prohibited' => 'The selected Department and Section description already exists.',
            'description.required' => 'The Department Section is required.',
            'description.regex' => 'The Section should not contain any special characters.',
            'description.max' => 'The Section must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $department_sections = DepartmentSection::create([
            'link_dept_id' => $request->department_id,
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $department_sections,
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
        $department_sections = DepartmentSection::
        with(['personnel', 'department'])
        ->where('id', $id)
        ->first();

        if( !$department_sections) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $department_sections,
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
        $department_sections = DepartmentSection::find($id);

        if( !$department_sections ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'department_id' => [
                'required',
                'exists:tblo_dept,id',
                Rule::prohibitedIf(DepartmentSection::where('link_dept_id', $request->department_id)
                                ->where('description', $request->description)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'description' => 'required|max:50|regex:/^[A-Za-z0-9,\- ]+$/'
        ],
        [
            'department_id.required' => 'The Department is required.',
            'department_id.exists' => 'The selected Department is invalid.',
            'department_id.prohibited' => 'The selected Department and Section description already exists.',
            'description.required' => 'The Department Section is required.',
            'description.regex' => 'The Section should not contain any special characters.',
            'description.max' => 'The Section must not exceed 50 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        DepartmentSection::where('id', $id)->update([
            'link_dept_id' => $request->department_id,
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => DepartmentSection::find($id)
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
        $department_sections = DepartmentSection::find($id);

        if( !$department_sections ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $department_sections->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
