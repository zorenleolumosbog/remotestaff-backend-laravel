<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\DepartmentSectionPersonnel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DepartmentSectionPersonnelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $department_section_personnel = DepartmentSectionPersonnel::
        with(['section'])
        ->with(['registrant' => function ($query) {
            $query->with(['basicInfo' => function ($query) {
                $query->selectRaw(DB::raw('CONCAT_WS(" ", reg_firstname, reg_lastname) as complete_name'))
                    ->select('reg_id', 'reg_link_preregid', 'reg_firstname', 'reg_lastname');
            }]);
        }])
        ->orWhereHas('section', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('description', 'LIKE', "{$request->search}%");
            });
        })
        ->orWhereHas('registrant.basicInfo', function (Builder $query) use ($request) {
            $query->when($request->search, function ($query) use ($request) {
                $query->where('reg_firstname', 'LIKE', "{$request->search}%");
                $query->orWhere(DB::raw('CONCAT_WS(" ", reg_firstname, reg_lastname)'), 'LIKE', "{$request->search}%");
            });
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : DepartmentSectionPersonnel::count());

        if( $department_section_personnel->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $department_section_personnel,
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
            'section_id' => [
                'required',
                'exists:tblo_dept_sec,id',
                Rule::prohibitedIf(DepartmentSectionPersonnel::where('link_sec_id', $request->section_id)
                                ->where('link_prereg_id', $request->registrant_id)
                                ->exists())
            ],
            'registrant_id' => 'required|exists:tblm_a_onboard_prereg,id'
        ],
        [
            'section_id.required' => 'The Section is required.',
            'section_id.exists' => 'The selected Section is invalid.',
            'section_id.prohibited' => 'The selected Section, Personnel, and Personnel description already exists.',
            'registrant_id.required' => 'The Personnel is required.',
            'registrant_id.exists' => 'The selected Personnel is invalid.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $department_section_personnel = DepartmentSectionPersonnel::create([
            'link_sec_id' => $request->section_id,
            'link_prereg_id' => $request->registrant_id,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $department_section_personnel,
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
        $department_section_personnel = DepartmentSectionPersonnel::
        with(['section'])
        ->with(['registrant' => function ($query) {
            $query->with(['basicInfo' => function ($query) {
                $query->select('reg_id', 'reg_link_preregid', 'reg_firstname', 'reg_lastname');
            }]);
        }])
        ->where('id', $id)
        ->first();

        if( !$department_section_personnel) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $department_section_personnel,
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
        $department_section_personnel = DepartmentSectionPersonnel::find($id);

        if( !$department_section_personnel ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'section_id' => [
                'required',
                'exists:tblo_dept_sec,id',
                Rule::prohibitedIf(DepartmentSectionPersonnel::where('link_sec_id', $request->section_id)
                                ->where('link_prereg_id', $request->registrant_id)
                                ->where('id', '!=', $id)
                                ->exists())
            ],
            'registrant_id' => 'required|exists:tblm_a_onboard_prereg,id'
        ],
        [
            'section_id.required' => 'The Section is required.',
            'section_id.exists' => 'The selected Section is invalid.',
            'section_id.prohibited' => 'The selected Section, Personnel, and Personnel description already exists.',
            'registrant_id.required' => 'The Personnel is required.',
            'registrant_id.exists' => 'The selected Personnel is invalid.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        DepartmentSectionPersonnel::where('id', $id)->update([
            'link_sec_id' => $request->section_id,
            'link_prereg_id' => $request->registrant_id,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => DepartmentSectionPersonnel::find($id)
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
        $department_section_personnel = DepartmentSectionPersonnel::find($id);

        if( !$department_section_personnel ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $department_section_personnel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
