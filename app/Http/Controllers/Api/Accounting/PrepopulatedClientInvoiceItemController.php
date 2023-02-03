<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Client;
use App\Models\Accounting\ClientInvoiceDetail;
use App\Models\Accounting\ClientInvoiceDetail2Add;
use App\Models\Accounting\ClientInvoiceItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PrepopulatedClientInvoiceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function clientInvoiceHeader()
    {
        $client_invoice_headers = Client::
        with(['invoiceHeaders'])
        ->has('invoiceHeaders')
        ->orderBy('client_poc', 'asc')
        ->get();

        if( $client_invoice_headers->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $client_invoice_headers,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function clientInvoiceHeaderDetail(Request $request, $id)
    {
        $client_invoice_header = DB::connection('mysql2')->table("tblt_invoice_hdr")
                                ->where('id', '=', $id)
                                ->select('due_date')
                                ->first();

        $client_invoice_header_details = ClientInvoiceDetail::
        where('link_inv_hdr', $id)
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : ClientInvoiceDetail::count());

        return response()->json([
            'success' => true,
            'header' => $client_invoice_header,
            'data' => $client_invoice_header_details,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeClientInvoiceHeaderItemDetail(Request $request)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'invoice_hdr_id' => 'required|exists:mysql2.tblt_invoice_hdr,id',
            'invoice_item_type_id' => 'required|exists:mysql.tblm_invoice_item_type,id',
            'particular' => 'required|max:30',
            'amount_add_on' => 'required|numeric'
        ],
        [
            'invoice_hdr_id.required' => 'The invoice header is required.',
            'invoice_item_type_id.required' => 'The invoice item type is required.',
            'invoice_hdr_id.exists' => 'The selected invoice hdr is invalid.',
            'invoice_item_type_id.exists' => 'The selected invoice item type is invalid.',
            'particular.required' => 'The invoice item particular is required.',
            'particular.max' => 'The invoice item particular must not exceed 30 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $invoice_items = ClientInvoiceDetail2Add::create([
            'link_invoice_hdr_id' => $request->invoice_hdr_id,
            'link_invoice_item_type_id' => $request->invoice_item_type_id,
            'particular' => $request->particular,
            'amount_add_on' => $request->amount_add_on,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

        $this->recomputeInvoiceTotal($request->invoice_hdr_id);

		return response()->json([
                'success' => true,
                'message' => 'Successfully added.',
                'data' => $invoice_items,
            ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function clientInvoiceHeaderItemDetail($id)
    {
        $invoice_items = ClientInvoiceDetail::
        where('id', $id)
        ->first();

        if( !$invoice_items) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice_items,
        ], 200);
    }

    public function updateInvoiceDueDate(Request $request) {
        $due_date = $request->query('due_date');
        $due_date = date("Y-m-d", strtotime($due_date));
        $id = $request->query('id');

        DB::connection('mysql2')->table('tblt_invoice_hdr')
              ->where('id', $id)
              ->update(['due_date' => $due_date]);

        return response()->json([
            'success' => true
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateClientInvoiceHeaderItemDetail(Request $request, $id)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'invoice_hdr_id' => 'required|exists:mysql2.tblt_invoice_hdr,id',
            'particular' => 'required|max:30',
            'billable_amt' => 'required|numeric'
        ],
        [
            'invoice_hdr_id.required' => 'The invoice header is required.',
            'invoice_hdr_id.exists' => 'The selected invoice hdr is invalid.',
            'particular.required' => 'The invoice item particular is required.',
            'particular.max' => 'The invoice item particular must not exceed 30 characters.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        ClientInvoiceDetail::where('id', $id)->update([
            'link_inv_hdr' => $request->invoice_hdr_id,
            'particular' => $request->particular,
            'billable_amt' => $request->billable_amt,
            'hours_rendered' => $request->hours_rendered,
            'rate_per_hour' => $request->rate_per_hour,
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        $this->recomputeInvoiceTotal($request->invoice_hdr_id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated.',
            'data' => ClientInvoiceDetail::find($id)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteClientInvoiceHeaderItemDetail($id)
    {
        $invoice_items = ClientInvoiceDetail2Add::find($id);

        $invoiceHeader = DB::connection('mysql2')->table('tblt_invoice_dtl_2_add')
                            ->where('id', '=', $id)
                            ->select(['link_invoice_hdr_id'])
                            ->first();

        $invoice_items->delete();

        $this->recomputeInvoiceTotal($invoiceHeader->link_invoice_hdr_id);

        if( !$invoice_items ) {
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted.',
        ], 200);
    }

    private function recomputeInvoiceTotal($id)
    {
        $invoiceDetails = DB::connection('mysql2')->table('tblt_invoice_dtl')
        ->where('tblt_invoice_dtl.link_inv_hdr', '=', $id)
        ->select(['billable_amt'])
        ->get();

        $invoiceAddtlDetails = DB::connection('mysql2')->table('tblt_invoice_dtl_2_add')
                ->join('tblt_invoice_hdr','tblt_invoice_hdr.id','=','tblt_invoice_dtl_2_add.link_invoice_hdr_id')
                ->where('link_invoice_hdr_id', '=', $id)
                ->where('tblt_invoice_dtl_2_add.particular', 'not like', '%gst%')
                ->where('tblt_invoice_dtl_2_add.is_void', '=', NULL)
                ->select(['amount_add_on'])
                ->get();

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
            $main_db = 'rs_ges_preprod';
        }
        elseif (config('app')['env'] == 'prod') {
            $main_db = 'rs_ges_prod';
        }

        $getGst = DB::connection('mysql2')->table('tblt_invoice_dtl_2_add')
                ->join('tblt_invoice_hdr','tblt_invoice_hdr.id','=','tblt_invoice_dtl_2_add.link_invoice_hdr_id')
                ->join($main_db.'.tblm_invoice_item_type', $main_db.'.tblm_invoice_item_type.id','=','tblt_invoice_dtl_2_add.link_invoice_item_type_id')
                ->where('link_invoice_hdr_id', '=', $id)
                ->where($main_db.'.tblm_invoice_item_type.description', 'like', '%gst%')
                ->where('tblt_invoice_dtl_2_add.is_void', '=', NULL)
                ->select([$main_db.'.tblm_invoice_item_type.percentage'])
                ->first();

        $total_amt = 0;
        foreach ($invoiceDetails as $key => $row) {
            $total_amt += $row->billable_amt;
        }

        foreach ($invoiceAddtlDetails as $key => $row) {
            $total_amt += $row->amount_add_on;
        }

        if(isset($getGst->percentage) && $getGst->percentage != '' && !empty($getGst->percentage)) {
            $total_amt = $total_amt - ($total_amt * ($getGst->percentage/100));
        }

        DB::connection('mysql2')->table('tblt_invoice_hdr')
        ->where('id', $id)
        ->update(['gross_amt' => $total_amt, 'net_amt' => $total_amt]);
    }
}
