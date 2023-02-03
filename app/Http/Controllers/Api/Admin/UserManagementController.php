<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminUserVerify;
use App\Models\Admin\UserManagement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_managements = UserManagement::
        when($request->search, function ($query) use ($request) {
            $query->where('email', 'LIKE', "{$request->search}%")
            ->orWhere('firstname', 'LIKE', "{$request->search}%")
            ->orWhere('middlename', 'LIKE', "{$request->search}%")
            ->orWhere('lastname', 'LIKE', "{$request->search}%");
        })
        ->with(['userRole'])
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : UserManagement::count());

        if( $user_managements->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $user_managements,
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
            'admin_user_role_id' => 'sometimes|exists:tblm_admin_user_role,id',
            'email' => 'required|unique:tblm_admin_user,email',
            'password' => 'required|confirmed',
            'firstname' => 'required|max:50',
            'middlename' => 'nullable|max:50',
            'lastname' => 'required|max:50'
        ],
        [
            'admin_user_role_id.exists' => 'The user role is invalid.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return [
                'errors' => $validator->errors()
            ];
		}

        //Create user management
        $user_managements = UserManagement::create([
            'link_admin_user_role_id' => $request->admin_user_role_id,
            'email'        => $request->email,
            'password'     => bcrypt($request->password),
            'firstname'    => $request->firstname,
            'middlename'   => $request->middlename,
            'lastname'     => $request->lastname,
            'createdby'    => auth()->user()->id,
            'datecreated'  => Carbon::now()
        ]);

        //Generate token for email
		$token = Str::random(64);
		
		//Create user verification token
		AdminUserVerify::create([
            'link_admin_user_id' => $user_managements->id, 
            'token' => $token,
            'datecreated'  => Carbon::now()
        ]);

        //Send email verification to the admin user
        Mail::send('email.adminEmailVerificationEmail', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Account activation - Remote Staff');
        });
		
		return response()->json([
					'success' => true,
					'message' => 'Successfully Added.',
					'data' => $user_managements,
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
        $user_managements = UserManagement::
        where('id', $id)
        ->first();

        if( !$user_managements) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $user_managements,
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
        $user_managements = UserManagement::find($id);
        
        if( !$user_managements ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'admin_user_role_id' => 'required|exists:tblm_admin_user_role,id',
            'email' => [
                Rule::prohibitedIf(UserManagement::where('email', $request->email)
                ->where('id', '!=', $id)->exists()),
                'required'
            ],
            'password' => 'confirmed',
            'firstname' => 'required|max:50',
            'middlename' => 'nullable|max:50',
            'lastname' => 'required|max:50'
        ],
        [
            'admin_user_role_id.required' => 'The user role is required.',
            'admin_user_role_id.exists' => 'The user role is invalid.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        //Update admin user info if without password
        if (!$request->password || empty($request->password)) {
            UserManagement::where('id', $id)->update([
                'link_admin_user_role_id' => $request->admin_user_role_id,
                'email'     => $request->email,
                'firstname'   => $request->firstname,
                'middlename'  => $request->middlename,
                'lastname'    => $request->lastname,
                'modifiedby'   => auth()->user()->id,
                'datemodified' => Carbon::now()
            ]);
        } else {
            //Update admin user info if password is set
            UserManagement::where('id', $id)->update([
                'link_admin_user_role_id' => $request->admin_user_role_id,
                'email'     => $request->email,
                'password'  => bcrypt($request->password),
                'firstname'   => $request->firstname,
                'middlename'  => $request->middlename,
                'lastname'    => $request->lastname,
                'modifiedby'   => auth()->user()->id,
                'datemodified' => Carbon::now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully Updated.',
            'data' => UserManagement::find($id)
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
        $user_managements = UserManagement::find($id);
        
        if( !$user_managements ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $user_managements->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully Deleted.',
        ], 200);
    }
}
