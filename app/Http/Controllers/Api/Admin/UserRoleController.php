<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserRole;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_roles = UserRole::
        when($request->search, function ($query) use ($request) {
            $query->where('description', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : UserRole::count());

        if( $user_roles->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $user_roles,
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
            'description' => 'required|max:20|unique:tblm_admin_user_role,description'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $user_roles = UserRole::create([
            'description' => $request->description,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully Added.',
					'data' => $user_roles,
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
        $user_roles = UserRole::
        where('id', $id)
        ->first();

        if( !$user_roles) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $user_roles,
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
        $user_roles = UserRole::find($id);
        
        if( !$user_roles ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(UserRole::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:20'
            ]
        ],
        [
            'description.prohibited' => 'The description has already been taken.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        UserRole::where('id', $id)->update([
            'description' => $request->description,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully Updated.',
            'data' => UserRole::find($id)
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
        //Check user role if currenly in use from other table
        if(UserRole::has('adminUsers')->where('id', $id)->exists()) {
            return response()->json([
				'success' => false,
				'message' => 'Failed to remove user role due to currently in use from the other table.',
			], 200);
        }

        $user_roles = UserRole::find($id);
        
        if( !$user_roles ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $user_roles->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully Deleted.',
        ], 200);
    }
}
