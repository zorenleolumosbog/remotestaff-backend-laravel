<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AccountType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        Builder::macro('whereLike', function($columns, $search) {
            $this->where(function($query) use ($columns, $search) {
                foreach(Arr::wrap($columns) as $column) {
                    $query->orWhere($column, 'LIKE', "{$search}%");
                }
            });

            return $this;
        });

        $account_type = AccountType::
        when($request->search, function ($query) use ($request) {
            $query->whereLike(['tblm_account_type.id','tblm_account_type.description', 'tblm_account_classification.description'], $request->search);
        })
        ->select('tblm_account_type.*', 'tblm_account_classification.description as acct_class_desc')
        ->join('tblm_account_classification', 'tblm_account_type.link_acct_class_id', '=', 'tblm_account_classification.id')
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : AccountType::count());

        if( $account_type->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $account_type,
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
            'description' => 'required|max:50|:tblm_account_type,description|regex:/^[A-Za-z0-9,\- ]+$/',
            'link_acct_class_id' => 'required:tblm_account_type,link_acct_class_id',
        ],
        [
            'description.required' => 'Description is requried.',
            'description.regex' => 'The Description should not contain any special characters.',
            'description.max' => 'Description must not exceed 50 characters.',
            'link_acct_class_id.required' => 'Classification is requried.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $has_data = AccountType::where('link_acct_class_id', '=', $request->link_acct_class_id)
        ->where('description', '=', $request->description)
        ->get();

        if ( count($has_data) > 0 ) {
            return response()->json([
                'errors' => array('description' => ['This record has already been added.'])
            ], 422);
        }

        $account_type = AccountType::create([
            'description' => $request->description,
            'link_acct_class_id' => $request->link_acct_class_id,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $account_type,
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
        $account_type = AccountType::
        where('id', $id)
        ->first();

        if( !$account_type) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $account_type,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Admin\AccountType  $accountType
     * @return \Illuminate\Http\Response
     */
    public function edit(AccountType $accountType)
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
        $account_type = AccountType::find($id);

        if( !$account_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'description' => [
                Rule::prohibitedIf(AccountType::where('description', $request->description)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:50',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ]
        ],
        [
            'description.required' => 'Description is requried.',
            'description.regex' => 'The Description should not contain any special characters.',
            'description.max' => 'Description must not exceed 50 characters.',
            'description.prohibited' => 'This record has already been added.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $has_data = AccountType::where('link_acct_class_id', '=', $request->link_acct_class_id)
        ->where('description', '=', $request->description)
        ->where('id', '!=', $id)
        ->get();

        if ( count($has_data) > 0 ) {
            return response()->json([
                'errors' => array('description' => ['This record has already been added.'])
            ], 422);
        }

        AccountType::where('id', $id)->update([
            'description' => $request->description,
            'link_acct_class_id' => $request->link_acct_class_id,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => AccountType::find($id),
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin\AccountType  $account_type
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $account_type = AccountType::find($id);

        if( !$account_type ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $account_type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
