<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\ClientRemoteContractor;
use App\Models\Admin\SubcontractorSched;
use App\Models\Admin\ClientSched;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
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
                    $query->orWhere($column, 'LIKE', "%{$search}%");
                }
            });

            return $this;
        });

		$subcontractor = ClientRemoteContractor::
        when($request->search, function ($query) use ($request) {
            $query->whereLike(['tblm_client_sub_contractor.id', 'tblm_b_onboard_actreg_basic.reg_firstname', 'tblm_b_onboard_actreg_basic.reg_lastname'], $request->search);
        })
        ->join('tblm_a_onboard_prereg', 'tblm_client_sub_contractor.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->join('tblm_b_onboard_actreg_basic', 'tblm_a_onboard_prereg.id', '=', 'tblm_b_onboard_actreg_basic.reg_link_preregid')
        ->select('tblm_client_sub_contractor.*', 'tblm_a_onboard_prereg.email as personal_email', 'tblm_b_onboard_actreg_basic.reg_firstname','tblm_b_onboard_actreg_basic.reg_lastname')
        ->orderBy('tblm_client_sub_contractor.id', 'desc')
        ->paginate($request->limit ? $request->limit : ClientRemoteContractor::count());

        return response()->json([
            'success' => true,
            'data' => $subcontractor,
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subcontractor = ClientRemoteContractor::
        with(['subcon_sched','client_sched',])
        ->join('tblm_a_onboard_prereg', 'tblm_client_sub_contractor.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->join('tblm_b_onboard_actreg_basic', 'tblm_a_onboard_prereg.id', '=', 'tblm_b_onboard_actreg_basic.reg_link_preregid')
        ->select('tblm_client_sub_contractor.*', 'tblm_a_onboard_prereg.email as personal_email', 'tblm_b_onboard_actreg_basic.reg_firstname','tblm_b_onboard_actreg_basic.reg_lastname')
        ->where('tblm_client_sub_contractor.id', $id)
        ->first();

        if( !$subcontractor ) {
            return response()->json([
				'success' => false,
				'message' => $subcontractor,
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $subcontractor,
        ], 200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
