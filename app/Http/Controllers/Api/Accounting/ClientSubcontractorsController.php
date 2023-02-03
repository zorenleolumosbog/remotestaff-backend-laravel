<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ClientSubcontractors;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class ClientSubcontractorsController extends Controller
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

        if (config('app')['env'] == 'local') {
            $main_db = 'rs_ges_preprod';
        }
        elseif (config('app')['env'] == 'dev') {
            $main_db = 'rs_ges_dev';
        }
        elseif (config('app')['env'] == 'staging') {
            $main_db = 'rs_ges_stg';
        }
        elseif (config('app')['env'] == 'uat') {
            $main_db = 'rs_ges_uat';
        }
        elseif (config('app')['env'] == 'preprod') {
            $main_db = 'rs_preprod_ges';
        }
        elseif (config('app')['env'] == 'prod') {
            $main_db = 'rs_ges_prod';
        }

		$clientSubcontractors = ClientSubcontractors::
        when($request->search, function ($query) use ($request, $main_db) {
            $query->whereLike(['link_client_id','link_subcon_id', 'client_name', $main_db.'.tblm_b_onboard_actreg_basic.reg_firstname', $main_db.'.tblm_b_onboard_actreg_basic.reg_lastname'], $request->search);
        })
        ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
        ->join($main_db.'.tblm_client_sub_contractor',$main_db.'.tblm_client_sub_contractor.id','=','tblm_client_subcon_pers.link_subcon_id')
        ->join($main_db.'.tblm_b_onboard_actreg_basic',$main_db.'.tblm_b_onboard_actreg_basic.reg_id','=',$main_db.'.tblm_client_sub_contractor.actreg_contractor_id')
        ->select('tblm_client_subcon_pers.id', 'link_client_id','link_subcon_id', 'client_name', DB::raw('CONCAT('.$main_db.'.tblm_b_onboard_actreg_basic.reg_firstname, " ", '.$main_db.'.tblm_b_onboard_actreg_basic.reg_lastname) AS subcon_name'))
        ->orderBy('tblm_client_subcon_pers.id', 'desc')
        ->paginate($request->limit ? $request->limit : ClientSubcontractors::count());

        return response()->json([
            'success' => true,
            'data' => $clientSubcontractors,
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
        $clientSubcontractors = ClientSubcontractors::create(
            [
                'link_client_id' => $request->client,
                'link_subcon_id' => $request->subcon,
                'createdby' => auth()->user()->id,
                'datecreated' => Carbon::now()
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $clientSubcontractors,
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        ClientSubcontractors::where('id', $request->id)->update(
            [
                'link_client_id' => $request->client,
                'link_subcon_id' => $request->subcon,
                'modifiedby' => auth()->user()->id,
                'datemodified' => Carbon::now()
            ]
        );

        return response()->json([
            'success' => true,
            'data' => ClientSubcontractors::find($request->id),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
		$clientSubcontractors = ClientSubcontractors::where('id', '=', $request->id)->first();
		
		if($clientSubcontractors===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete client
			$clientSubcontractors->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted client-subcontractor.',
			], 200);
		}
    }

    public function clientList(Request $request)
    {
        $client = DB::connection('mysql2')->table("tblm_client")
            ->orderBy('id', 'ASC')
            ->get();

        $subcontructors = DB::connection('mysql2')->table("tblm_client_subcon_pers")
            ->orderBy('id', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $client,
        ], 200);
    }

    public function subconList(Request $request)
    {
        $client = DB::connection('mysql')->table("tblm_client_sub_contractor")
            ->join('tblm_b_onboard_actreg_basic','tblm_b_onboard_actreg_basic.reg_id','=','tblm_client_sub_contractor.actreg_contractor_id')
            ->select('tblm_client_sub_contractor.id', 'tblm_b_onboard_actreg_basic.reg_firstname', 'tblm_b_onboard_actreg_basic.reg_lastname')
            ->orderBy('id', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $client,
        ], 200);
    }
}
