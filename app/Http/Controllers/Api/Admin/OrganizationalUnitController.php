<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\OrganizationalUnit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrganizationalUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $organizational_unit = OrganizationalUnit::
        with(['towncity' => function ($query) {
            $query->get();

        }])
        ->when($request->search, function ($query) use ($request) {
            $query->where('ou_name', 'LIKE', "{$request->search}%");
            $query->orWhere('registered_legal_name', 'LIKE', "{$request->search}%");
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : OrganizationalUnit::count());

        if( $organizational_unit->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $organizational_unit,
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
            'ou_name' => 'required|max:50|unique:tblm_organizational_unit,ou_name|regex:/^[A-Za-z0-9,\- ]+$/',
            'registered_legal_name' => 'required|max:50|unique:tblm_organizational_unit,registered_legal_name|regex:/^[A-Za-z0-9,\- ]+$/',
            'address_line1' => 'required|max:50',
            'address_line2' => 'nullable|max:50',
            'town_city' => 'required|exists:tblm_towncity,id',
            'is_hq' => 'required|boolean'
        ],
        [
            'ou_name.required' => 'The Organizational unit name is required.',
            'ou_name.regex' => 'The Organizational unit name should not contain any special characters.',
            'ou_name.unique' => 'The Organizational name already exists.',
            'ou_name.max' => 'The Organizational unit name must not be greater than 50 characters.',
            'registered_legal_name.required' => 'The Registered legal name is required.',
            'registered_legal_name.regex' => 'The Registered legal name should not contain any special characters.',
            'registered_legal_name.unique' => 'The Registered legal name already exists.',
            'registered_legal_name.max' => 'The Registered legal name must not be greater than 50 characters.',
            'address_line1.required' => 'The Address Line 1 is required.',
            'address_line1.max' => 'The Address Line 1 must not be greater than 50 characters.',
            'address_line2.max' => 'The Address Line 2 must not be greater than 50 characters.',
            'town_city.required' => 'The Town/City is requried.',
            'town_city.exists' => 'The Town/City does not exists.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $organizational_unit = OrganizationalUnit::create([
            'ou_name' => $request->ou_name,
            'registered_legal_name' => $request->registered_legal_name,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'town_city' => $request->town_city,
            'is_hq' => $request->is_hq,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

		return response()->json([
					'success' => true,
					'message' => 'Successfully added.',
					'data' => $organizational_unit,
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
        $organizational_unit = OrganizationalUnit::
        with(['towncity' => function ($query) {
            $query->with(['state' => function ($query) {
                $query->with(['country' => function ($query) {
                    $query->first();

                }])
                ->with(['region' => function ($query) {
                    $query->with(['country' => function ($query) {
                        $query->first();

                    }])
                    ->first();

                }])
                ->first();

            }])
            ->first();

        }])
        ->where('id', $id)
        ->first();

        if( !$organizational_unit) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $organizational_unit,
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
        $organizational_unit = OrganizationalUnit::find($id);

        if( !$organizational_unit ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        //Set validation
		$validator = Validator::make($request->all(), [
            'ou_name' => [
                Rule::prohibitedIf(OrganizationalUnit::where('ou_name', $request->ou_name)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:50',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ],
            'registered_legal_name' => [
                Rule::prohibitedIf(OrganizationalUnit::where('registered_legal_name', $request->registered_legal_name)
                ->where('id', '!=', $id)->exists()),
                'required',
                'max:50',
                'regex:/^[A-Za-z0-9,\- ]+$/'
            ],
            'address_line1' => 'required|max:50',
            'address_line2' => 'nullable|max:50',
            'town_city' => 'required|exists:tblm_towncity,id',
            'is_hq' => 'required|boolean'
        ],
        [
            'ou_name.required' => 'The Organizational unit name is required.',
            'ou_name.regex' => 'The Organizational unit name should not contain any special characters.',
            'ou_name.prohibited' => 'The Organizational name already exists.',
            'ou_name.max' => 'The Organizational unit name must not be greater than 50 characters.',
            'registered_legal_name.required' => 'The Registered legal name is required.',
            'registered_legal_name.regex' => 'The Registered legal name should not contain any special characters.',
            'registered_legal_name.prohibited' => 'The Registered legal name already exists.',
            'registered_legal_name.max' => 'The Registered legal name must not be greater than 50 characters.',
            'address_line1.required' => 'The Address Line 1 is required.',
            'address_line1.max' => 'The Address Line 1 must not be greater than 50 characters.',
            'address_line2.max' => 'The Address Line 2 must not be greater than 50 characters.',
            'town_city.required' => 'The Town/City is requried.',
            'town_city.exists' => 'The Town/City does not exists.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        OrganizationalUnit::where('id', $id)->update([
            'ou_name' => $request->ou_name,
            'registered_legal_name' => $request->registered_legal_name,
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'town_city' => $request->town_city,
            'is_hq' => $request->is_hq,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => OrganizationalUnit::find($id),
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
        $organizational_unit = OrganizationalUnit::find($id);

        if( !$organizational_unit ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $organizational_unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
