<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ForexRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ForexRateController extends Controller
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
            $main_db = 'rs_ges_prod';
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
            $main_db = 'rs_ges_preprod';
        }
        elseif (config('app')['env'] == 'prod') {
            $main_db = 'rs_ges_prod';
        }

        $forex_rate = ForexRate::
            when($request->search, function ($query) use ($request) {
                $query->whereLike(['tblm_forex_for_client_invoice.id','tblm_forex_for_client_invoice.rate', 'tblm_currency.code', 'tblm_forex_for_client_invoice.effective_month_year', 'tblm_forex_rate_type.description'], $request->search);
            })
            ->select([
            'tblm_forex_for_client_invoice.*',  DB::raw('DATE_FORMAT(tblm_forex_for_client_invoice.effective_month_year, "%M %Y") as effective_date'), 
            'tblm_forex_rate_type.description as forex_description', $main_db.'.tblm_currency.code as currency_description', 
            DB::raw('DATE_FORMAT(tblm_forex_for_client_invoice.datecreated, "%M %Y") as date_created')])
            ->join('tblm_forex_rate_type', 'tblm_forex_rate_type.id', '=', 'tblm_forex_for_client_invoice.forex_rate_type_id')
            ->join($main_db.'.tblm_currency', $main_db.'.tblm_currency.id', '=', 'tblm_forex_for_client_invoice.currency_id')
            // ->join($main_db.'.tblm_a_onboard_prereg', $main_db.'.tblm_a_onboard_prereg.id', '=', 'tblm_forex_for_client_invoice.createdby')
            // ->join($main_db.'.tblm_b_onboard_actreg_basic', $main_db.'.tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
            ->where('tblm_forex_for_client_invoice.isActive', '=', 1)
            ->orderBy('tblm_forex_for_client_invoice.id', 'DESC')
            ->paginate($request->limit ? $request->limit : ForexRate::count());

        return response()->json([
            'success' => true,
            'data' => $forex_rate,
        ], 200);
    }

    public function getForexRateHistory(Request $request)
    { 
        if (config('app')['env'] == 'local') {
            $main_db = 'rs_ges_prod';
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
            $main_db = 'rs_ges_preprod';
        }
        elseif (config('app')['env'] == 'prod') {
            $main_db = 'rs_ges_prod';
        }

        $forex_rate = ForexRate::
            select([
            'tblm_forex_for_client_invoice.*',  DB::raw('DATE_FORMAT(tblm_forex_for_client_invoice.effective_month_year, "%M %Y") as effective_date'), 
            'tblm_forex_rate_type.description as forex_description', $main_db.'.tblm_currency.code as currency_description',
            $main_db.'.tblm_b_onboard_actreg_basic.reg_firstname', $main_db.'.tblm_b_onboard_actreg_basic.reg_lastname',
            DB::raw('DATE_FORMAT(tblm_forex_for_client_invoice.datecreated, "%M %Y") as date_created')])
            ->join('tblm_forex_rate_type', 'tblm_forex_rate_type.id', '=', 'tblm_forex_for_client_invoice.forex_rate_type_id')
            ->join($main_db.'.tblm_currency', $main_db.'.tblm_currency.id', '=', 'tblm_forex_for_client_invoice.currency_id')
            ->leftjoin($main_db.'.tblm_a_onboard_prereg', $main_db.'.tblm_a_onboard_prereg.id', '=', 'tblm_forex_for_client_invoice.createdby')
            ->leftjoin($main_db.'.tblm_b_onboard_actreg_basic', $main_db.'.tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
            ->orderBy('tblm_forex_for_client_invoice.id', 'DESC')
            ->paginate($request->limit ? $request->limit : ForexRate::count());

        return response()->json([
            'success' => true,
            'data' => $forex_rate,
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
        // Set validation
		$validator = Validator::make($request->all(), [
            'rate' => 'required:mysql2.tblm_forex_for_client_invoice',
            'effective_month_year' => 'required:mysql2.tblm_forex_for_client_invoice',
            'currency_id' => 'required:mysql2.tblm_forex_for_client_invoice',
            'forex_rate_type_id' => 'required:mysql2.tblm_forex_for_client_invoice',
        ],
        [
            'rate.required' => 'Rate is requried.',
            'effective_month_year.required' => 'Effectivity month and year is requried.',
            'currency_id.required' => 'Currency is requried.',
            'forex_rate_type_id.required' => 'Forex Rate Type is requried.',
        ]);


        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        // $effective_month_year = $request->effective_month_year.'-01';
        $effective_month_year = $request->effective_month_year;
        $date = date('Y-m-d', strtotime($effective_month_year));

        $result = DB::connection('mysql2')->table('tblm_forex_for_client_invoice')
                            ->where('effective_month_year','=', $effective_month_year)
                            ->where('forex_rate_type_id','=', $request->forex_rate_type_id)
                            ->where('currency_id','=', $request->currency_id)
                            ->where('isActive','=', 1)
                            ->select('id')
                            ->get();

        if(isset($result)) {
            foreach($result as $row) {
                DB::connection('mysql2')->table('tblm_forex_for_client_invoice')
                ->where('id', $row->id)
                ->update(['isActive' => 0]);
            }
        }

        $forex_rate = ForexRate::create(
            [
                'rate' => $request->rate,
                'effective_month_year' => $date,
                'currency_id' => $request->currency_id,
                'forex_rate_type_id' => $request->forex_rate_type_id,
                'isActive' => 1,
                'createdby' => auth()->user()->id,
                'datecreated' => Carbon::now()
            ]
        );

		$forex_rate = ForexRate::orderBy('id', 'desc')->paginate($request->limit);

        return response()->json([
            'success' => true,
            'data' => $forex_rate,
            'message' => 'Successfully saved.'
        ], 200);
    }

    public function getForexRate(Request $request, $id) {
        $result = DB::connection('mysql2')->table('tblm_forex_for_client_invoice')
                            ->where('id','=', $id)
                            // ->select(['tblm_forex_for_client_invoice.*',  DB::raw('DATE_FORMAT(tblm_forex_for_client_invoice.effective_month_year, "%Y-%m-%d") as effective_month_year')])
                            ->select(['tblm_forex_for_client_invoice.*',  'tblm_forex_for_client_invoice.effective_month_year as effective_month_year'])
                            // ->select(['tblm_forex_for_client_invoice.*',  DB::raw('DATE_FORMAT(tblm_forex_for_client_invoice.effective_month_year, "%m/%e/%Y") as month_year')])
                            ->first();

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $effective_month_year = $request->effective_month_year.'-01';
        $effective_month_year = $request->effective_month_year;
        $date = date('Y-m-d', strtotime($effective_month_year));

        $forex_rate = ForexRate::create(
            [
                'rate' => $request->rate,
                'effective_month_year' => $date,
                'currency_id' => $request->currency_id,
                'forex_rate_type_id' => $request->forex_rate_type_id,
                'isActive' => 1,
                'isEdited' => 1,
                'createdby' => auth()->user()->id,
                'datecreated' => Carbon::now()
            ]
        );

        DB::connection('mysql2')->table('tblm_forex_for_client_invoice')
                ->where('id', $request->id)
                ->update(['isActive' => 0]);

        return response()->json([
            'success' => true,
            'data' => ForexRate::find($request->id),
            'message' => 'Successfully updated.'
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
        $forex_rate = ForexRate::find($id);
        
        if( !$forex_rate ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        $forex_rate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }
}
