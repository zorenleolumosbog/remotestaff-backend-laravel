<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        if ($start_date != '' && $end_date != '') {
            $start_date = date("Y-m-d", strtotime($start_date));
            $end_date = date("Y-m-d", strtotime($end_date));
        }

        $getClientsWithGeneratedInvoice = DB::connection('mysql2')->table("tblt_timesheet_sumry")
            ->where('inv_period_from', '=', $start_date)
            ->where('inv_period_to', '=', $end_date)
            ->where('is_generated', '=', '1')
            ->select('link_client_id')
            ->get();

        $clientsWithGeneratedInvoice = [];
        foreach ($getClientsWithGeneratedInvoice as $client) {
            $clientsWithGeneratedInvoice[] = $client->link_client_id;
        }

        $client = DB::connection('mysql2')->table("tblm_client")
            ->whereNotIn('id', $clientsWithGeneratedInvoice)
            // ->whereIn('id', array(8087, 10978, 10441, 2633))
            // ->where('client_name', 'not like', '%default%')
            // ->where('client_name', 'not like', '%test%')
            // ->where('client_name', 'not like', '%none%')
            // ->where('client_name', 'not like', '%n/a%')
            // ->where('client_name', 'not like', '%...%')
            ->where('client_poc', 'not like', '%default%')
            ->where('client_poc', '!=', '')
            ->where('isdoubted', '=', 0)
            ->orderBy('client_poc', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $client,
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
            'client_name' => 'required:mysql2.tblm_client',
            'client_email' => 'required:mysql2.tblm_client',
            'client_ABN' => 'required:mysql2.tblm_client',
            'client_phone' => 'required:mysql2.tblm_client',
        ],
        [
            'client_name.required' => 'The Client Name is requried.',
            'client_email.required' => 'The Client Email is requried.',
            'client_ABN.required' => 'The Client ABN is requried.',
            'client_phone.required' => 'The Client Phone is requried.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $client = Client::create(
            [
                'client_name' => $request->client_name,
                'client_poc' => $request->client_poc,
                'client_poc_position' => $request->client_poc_position,
                'client_addr_line1' => $request->client_addr_line1,
                'client_addr_line2' => $request->client_addr_line2,
                'client_towncity' => $request->client_towncity,
                'client_email' => $request->client_email,
                'client_ABN' => $request->client_ABN,
                'client_phone' => $request->client_phone,
                'client_currency' => $request->client_currency,
                'createdby' => auth()->user()->id,
                'datecreated' => Carbon::now()
            ]
        );

		$client = Client::orderBy('id', 'desc')->paginate($request->limit);
        
        return response()->json([
            'success' => true,
            'data' => $client,
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
        // Set validation
		$validator = Validator::make($request->all(), [
            'client_name' => 'required:mysql2.tblm_client',
            'client_email' => 'required:mysql2.tblm_client',
            'client_ABN' => 'required:mysql2.tblm_client',
            'client_phone' => 'required:mysql2.tblm_client',
        ],
        [
            'client_name.required' => 'The Client Name is requried.',
            'client_email.required' => 'The Client Email is requried.',
            'client_ABN.required' => 'The Client ABN is requried.',
            'client_phone.required' => 'The Client Phone is requried.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        Client::where('id', $request->id)->update(
            [
                'client_name' => $request->client_name,
                'client_poc' => $request->client_poc,
                'client_poc_position' => $request->client_poc_position,
                'client_addr_line1' => $request->client_addr_line1,
                'client_addr_line2' => $request->client_addr_line2,
                'client_towncity' => $request->client_towncity,
                'client_email' => $request->client_email,
                'client_ABN' => $request->client_ABN,
                'client_phone' => $request->client_phone,
                'client_currency' => $request->client_currency,
                'modifiedby' => auth()->user()->id,
                'datemodified' => Carbon::now()
            ]
        );

        return response()->json([
            'success' => true,
            'data' => Client::find($request->id),
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
			Client::wherein('id', $request->id)->delete();

            $client = Client::orderBy('id', 'desc')->paginate($request->limit);
        
			return response()->json([
				'success' => true,
				'data' => $client,
			], 200);
    }

    
    public function clientPgn(Request $request)
    {

        Builder::macro('whereLike', function($columns, $search) {
            $this->where(function($query) use ($columns, $search) {
                foreach(Arr::wrap($columns) as $column) {
                    $query->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        
            return $this;
        });

		$client = Client::
        when($request->search, function ($query) use ($request) {
            $query->whereLike(['id','client_id_legacy','client_name','client_poc','client_poc_position'], $request->search);
        })
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : Client::count());

        return response()->json([
            'success' => true,
            'data' => $client,
        ], 200);
		
    }
}
