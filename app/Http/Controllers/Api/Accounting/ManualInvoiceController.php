<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Client;
use App\Models\Accounting\ClientInvoiceDetail;
use App\Models\Accounting\ClientInvoiceItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ManualInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function saveInvoiceHeader(Request $request) {
        $client = $request->query('client');
        $invoice_date = $request->query('invoice_date');
        $due_date = $request->query('due_date');
        $inv_period_from = $request->query('inv_period_from');
        $inv_period_to = $request->query('inv_period_to');

        $invoice_date = date("Y-m-d", strtotime($invoice_date));
        $due_date = date("Y-m-d", strtotime($due_date));
        $inv_period_from = date("Y-m-d", strtotime($inv_period_from));
        $inv_period_to = date("Y-m-d", strtotime($inv_period_to));

        $invoiceHeaderId = DB::connection('mysql2')->table('tblt_invoice_hdr')->insertGetId(
            ['link_client_id' => $client, 'inv_date' => $invoice_date, 'due_date' => $due_date, 'inv_period_from' => $inv_period_from, 'inv_period_to' => $inv_period_to, 'status_id' => 2, 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
        );

        return response()->json([
            'success' => true,
            'invoice_id' => $invoiceHeaderId
        ], 200);
    }

    public function saveInvoiceDetail(Request $request)
    {
        $invoice_items = ClientInvoiceDetail::create([
            'link_inv_hdr' => $request->invoice_hdr_id,
            'link_invoice_item_type_id' => $request->invoice_item_type_id,
            'particular' => $request->particular,
            'rate_per_hour' => $request->hourly_rate,
            'hours_rendered' => $request->total_hours,
            'billable_amt' => $request->amount_add_on,
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
