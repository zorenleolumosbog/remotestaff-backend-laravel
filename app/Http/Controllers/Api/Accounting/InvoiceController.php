<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mail;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $fpdf;

    public function index(Request $request)
    {
        $clients = $request->query('client');
        $clients = (explode(",",$clients)); 
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        $save_temp_invoice = $request->query('save_temp_invoice');
        $limit = $request->query('limit');
        $page = $request->query('page');
        $offset = ($limit-1)*$page;

        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        $getClientsWithGeneratedInvoice = DB::connection('mysql2')->table("tblt_timesheet_sumry")
            ->where('inv_period_from', '=', $start_date)
            ->where('inv_period_to', '=', $end_date)
            ->select('link_client_id')
            ->get();

        $getClientsWithApprovedInvoice = DB::connection('mysql2')->table("tblt_invoice_hdr")
            ->where('inv_period_from', '=', $start_date)
            ->where('inv_period_to', '=', $end_date)
            ->where('is_void', '=', NULL)
            ->select('link_client_id')
            ->get();

        //Remove Clients with Approved Invoice
        $clientsWithApprovedInvoice = [];
        foreach ($getClientsWithApprovedInvoice as $client) {
            $clientsWithApprovedInvoice[] = $client->link_client_id;
        }

        $selectedClientsWithApprovedInvoice = [];
        $selectedClientsWithoutApprovedInvoice = [];
        foreach ($clients as $client) {
            if (!in_array($client, $clientsWithApprovedInvoice)) {
                $selectedClientsWithoutApprovedInvoice[] = $client;
            }
            else {
                $selectedClientsWithApprovedInvoice[] = $client;
            }
        }

        $clients = $selectedClientsWithoutApprovedInvoice;

        //Check Clients with Generated Invoice to Return Warning
        // $clientsWithGeneratedInvoice = [];
        // foreach ($getClientsWithGeneratedInvoice as $client) {
        //     $clientsWithGeneratedInvoice[] = $client->link_client_id;
        // }

        // $selectedClientsWithGeneratedInvoice = [];
        // foreach ($clients as $client) {
        //     if (in_array($client, $clientsWithGeneratedInvoice)) {
        //         $selectedClientsWithGeneratedInvoice[] = $client;
        //     }
        // }

        if($save_temp_invoice=='true' && !empty($selectedClientsWithApprovedInvoice)) {
            $clients = DB::connection('mysql2')->table("tblm_client")
                ->whereIn('id', $selectedClientsWithApprovedInvoice)
                ->orderBy('id', 'ASC')
                ->get();

            $clientNames = [];
            foreach ($clients as $client) {
                $clientNames[] = $client->client_poc;
            }

            $clientNames = implode(", ", $clientNames);

            return response()->json([
                'success' => false,
                'message' => 'existing client',
                'clients_with_invoice' => $clientNames
            ], 200);
        }

        $paginated_data = $this->paginateTempInvoice($start_date, $end_date, $clients, $offset, $limit);
        $generatedData = $this->generateTempInvoice($start_date, $end_date, $clients);

        if ($save_temp_invoice=='true') {
            $checkIfHasTimesheet = DB::connection('mysql2')->table("tblt_timesheet_dtl")
                                    ->whereBetween('date_worked', [$start_date, $end_date])
                                    ->select('id')
                                    ->first();

            if (isset($checkIfHasTimesheet->id)) {

            }
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'no timesheet'
                ], 200);
            }
        }

        if ($save_temp_invoice=='true') {
            foreach ($generatedData as $row) {
                if (!empty($row->link_client_id)) {
                    // DB::connection('mysql2')->table('tblt_timesheet_sumry')->insert(
                    //     ['link_client_id' => $row->link_client_id, 'period_from' => $start_date, 'period_to' => $end_date, 'gross_billable_hours' => $row->work_total_hours, 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
                    // );

                    DB::connection('mysql2')->table('tblt_timesheet_sumry')->insert(
                        ['link_client_id' => $row->link_client_id, 'inv_period_from' => $start_date, 'inv_period_to' => $end_date, 'is_generated' => '0', 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $paginated_data
        ], 200);
    }

    public function approveInvoice(Request $request) 
    {
        $client = $request->query('client');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        $prev_month_start_date = date("Y-m-d", strtotime("-1 month", strtotime($start_date)));
        $prev_month_end_date = date("Y-m-d", strtotime("-1 month", strtotime($end_date)));

        $invoiceHeaderId = DB::connection('mysql2')->table('tblt_invoice_hdr')->insertGetId(
            ['link_client_id' => $client, 'inv_date' => date("Y-m-d"), 'inv_period_from' => $start_date, 'inv_period_to' => $end_date, 'status_id' => 2, 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
        );

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

        $invoiceDetails = DB::connection('mysql2')->table('tblt_timesheet_hdr')
                            ->leftJoin('tblt_timesheet_dtl','tblt_timesheet_dtl.link_tms_hdr','=','tblt_timesheet_hdr.id')
                            ->leftJoin('tblt_timesheet_adj_dtl','tblt_timesheet_adj_dtl.link_timesheet_dtl_id','=','tblt_timesheet_dtl.id')
                            ->join("tblm_client_subcon_pers", function($join) {
                                $join->on("tblm_client_subcon_pers.link_subcon_id","=","tblt_timesheet_hdr.link_subcon_id")
                                        ->on("tblm_client_subcon_pers.link_client_id","=","tblt_timesheet_hdr.link_client_id");
                            })
                            ->join('tblm_client_basic_rate','tblm_client_basic_rate.link_client_subcon_pers','=','tblm_client_subcon_pers.id')
                            ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
                            ->join($main_db.'.tblm_client_sub_contractor',$main_db.'.tblm_client_sub_contractor.id','=','tblt_timesheet_hdr.link_subcon_id')
                            ->join($main_db.'.tblm_b_onboard_actreg_basic',$main_db.'.tblm_b_onboard_actreg_basic.reg_id','=',$main_db.'.tblm_client_sub_contractor.actreg_contractor_id')
                            ->where('tblt_timesheet_hdr.link_client_id', '=', $client)
                            ->whereBetween('date_worked', [$start_date, $end_date])
                            ->select([$main_db.'.tblm_b_onboard_actreg_basic.reg_firstname', $main_db.'.tblm_b_onboard_actreg_basic.reg_lastname', 'tblt_timesheet_hdr.link_subcon_id', 'tblt_timesheet_hdr.link_client_id', 'tblm_client.client_name', DB::raw('SUM(tblt_timesheet_dtl.reg_ros_hours) AS work_total_hours'), DB::raw('SUM(case when (tblt_timesheet_adj_dtl.adjusted_hours IS NULL) THEN tblt_timesheet_dtl.reg_ros_hours ELSE tblt_timesheet_adj_dtl.adjusted_hours END) AS adjusted_total_hours'), 'tblm_client_basic_rate.basic_hourly_rate'])
                            ->groupBy('tblt_timesheet_hdr.link_subcon_id')
                            ->get();

        $totalInvoiceAmount = 0;
        foreach ($invoiceDetails as $row) {
            $invoice_amount = $row->work_total_hours * $row->basic_hourly_rate;

            DB::connection('mysql2')->table('tblt_invoice_dtl')->insert(
                ['link_inv_hdr' => $invoiceHeaderId, 'particular' => $row->reg_firstname.' '.$row->reg_lastname, 'hours_rendered' => $row->work_total_hours, 'rate_per_hour' => $row->basic_hourly_rate, 'billable_amt' => $invoice_amount, 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
            );
            $totalInvoiceAmount+=$invoice_amount;
        }

        $prevMonthInvoiceDetails = DB::connection('mysql2')->table('tblt_timesheet_hdr')
                            ->leftJoin('tblt_timesheet_dtl','tblt_timesheet_dtl.link_tms_hdr','=','tblt_timesheet_hdr.id')
                            ->leftJoin('tblt_timesheet_adj_dtl','tblt_timesheet_adj_dtl.link_timesheet_dtl_id','=','tblt_timesheet_dtl.id')
                            ->join("tblm_client_subcon_pers", function($join) {
                                $join->on("tblm_client_subcon_pers.link_subcon_id","=","tblt_timesheet_hdr.link_subcon_id")
                                        ->on("tblm_client_subcon_pers.link_client_id","=","tblt_timesheet_hdr.link_client_id");
                            })
                            ->join('tblm_client_basic_rate','tblm_client_basic_rate.link_client_subcon_pers','=','tblm_client_subcon_pers.id')
                            ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
                            ->join($main_db.'.tblm_client_sub_contractor',$main_db.'.tblm_client_sub_contractor.id','=','tblt_timesheet_hdr.link_subcon_id')
                            ->join($main_db.'.tblm_b_onboard_actreg_basic',$main_db.'.tblm_b_onboard_actreg_basic.reg_id','=',$main_db.'.tblm_client_sub_contractor.actreg_contractor_id')
                            ->where('tblt_timesheet_hdr.link_client_id', '=', $client)
                            ->whereBetween('date_worked', [$prev_month_start_date, $prev_month_end_date])
                            ->select([$main_db.'.tblm_b_onboard_actreg_basic.reg_firstname', $main_db.'.tblm_b_onboard_actreg_basic.reg_lastname', 'tblt_timesheet_hdr.link_subcon_id', 'tblt_timesheet_hdr.link_client_id', 'tblm_client.client_name', 'tblm_client.client_currency', DB::raw('SUM(tblt_timesheet_dtl.reg_ros_hours) AS work_total_hours'), DB::raw('SUM(case when (tblt_timesheet_adj_dtl.adjusted_hours IS NULL) THEN tblt_timesheet_dtl.reg_ros_hours ELSE tblt_timesheet_adj_dtl.adjusted_hours END) AS adjusted_total_hours'), 'tblm_client_basic_rate.basic_hourly_rate'])
                            ->groupBy('tblt_timesheet_hdr.link_subcon_id')
                            ->get();

        foreach ($prevMonthInvoiceDetails as $row) {
            $getReferenceRate = DB::connection('mysql2')->table('tblm_forex_for_client_invoice')
                            ->join('tblm_forex_rate_type','tblm_forex_rate_type.id','=','tblm_forex_for_client_invoice.forex_rate_type_id')
                            ->join($main_db.'.tblm_currency', $main_db.'.tblm_currency.id','=','tblm_forex_for_client_invoice.currency_id')
                            ->where($main_db.'.tblm_currency.id', '=', $row->client_currency)
                            ->where('tblm_forex_rate_type.description', 'like', '%reference%')
                            ->where('tblm_forex_for_client_invoice.isActive', '=', 1)
                            ->where('tblm_forex_for_client_invoice.effective_month_year', '=', $prev_month_start_date)
                            ->select(['tblm_forex_for_client_invoice.rate'])
                            ->first();

            $getActualRate = DB::connection('mysql2')->table('tblm_forex_for_client_invoice')
                            ->join('tblm_forex_rate_type','tblm_forex_rate_type.id','=','tblm_forex_for_client_invoice.forex_rate_type_id')
                            ->join($main_db.'.tblm_currency', $main_db.'.tblm_currency.id','=','tblm_forex_for_client_invoice.currency_id')
                            ->where($main_db.'.tblm_currency.id', '=', $row->client_currency)
                            ->where('tblm_forex_rate_type.description', 'like', '%actual%')
                            ->where('tblm_forex_for_client_invoice.isActive', '=', 1)
                            ->where('tblm_forex_for_client_invoice.effective_month_year', '=', $prev_month_start_date)
                            ->select(['tblm_forex_for_client_invoice.rate'])
                            ->first();

            $diff_adj_hours = $row->adjusted_total_hours - $row->work_total_hours;
            if ($diff_adj_hours < 0) {
                $particular = 'Adjustment Credit Memo';
            }
            elseif ($diff_adj_hours > 0){
                $particular = 'Adjustment Over Time Work';
            }
            else {}

            if ($diff_adj_hours != 0) {
                $invoice_amount = $diff_adj_hours * $row->basic_hourly_rate;
                if (isset($row->adjusted_total_hours) && $row->adjusted_total_hours != '' && !empty($row->adjusted_total_hours)) {
                    DB::connection('mysql2')->table('tblt_invoice_dtl')->insert(
                        ['link_inv_hdr' => $invoiceHeaderId, 'particular' => $particular.' ('.$row->reg_firstname.' '.$row->reg_lastname.')', 'hours_rendered' => $diff_adj_hours, 'rate_per_hour' => $row->basic_hourly_rate, 'billable_amt' => $invoice_amount, 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
                    );$totalInvoiceAmount+=$invoice_amount;
                }
            }
            if ($diff_adj_hours != 0 && isset($getReferenceRate->rate) && isset($getActualRate->rate)) {
                $currency_adj = ($getReferenceRate->rate-$getActualRate->rate)/$getActualRate->rate;
                $currency_adj_amount = ($diff_adj_hours*($getReferenceRate->rate-$getActualRate->rate)/$getActualRate->rate);
                if (isset($row->adjusted_total_hours) && $row->adjusted_total_hours != '' && !empty($row->adjusted_total_hours)) {
                    DB::connection('mysql2')->table('tblt_invoice_dtl')->insert(
                        ['link_inv_hdr' => $invoiceHeaderId, 'particular' => 'Currency Adjustment Item ('.$row->reg_firstname.' '.$row->reg_lastname.')', 'hours_rendered' => $diff_adj_hours, 'rate_per_hour' => $currency_adj, 'billable_amt' => $currency_adj_amount, 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
                    );
                    $totalInvoiceAmount+=$currency_adj_amount;
                }
            }
        }

        DB::connection('mysql2')->table('tblt_invoice_hdr')
              ->where('id', $invoiceHeaderId)
              ->update(['gross_amt' => $totalInvoiceAmount, 'net_amt' => $totalInvoiceAmount]);

        return response()->json([
            'success' => true
        ], 200);

    }

    private function paginateTempInvoice($start_date, $end_date, $clients, $offset, $limit) 
    {
        $result = DB::connection('mysql2')->table('tblt_timesheet_hdr')
                ->join('tblt_timesheet_dtl','tblt_timesheet_dtl.link_tms_hdr','=','tblt_timesheet_hdr.id')
                ->join("tblm_client_subcon_pers", function($join) {
                    $join->on("tblm_client_subcon_pers.link_subcon_id","=","tblt_timesheet_hdr.link_subcon_id")
                            ->on("tblm_client_subcon_pers.link_client_id","=","tblt_timesheet_hdr.link_client_id");
                })
                ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
                ->whereIn('tblt_timesheet_hdr.link_client_id', $clients)
                ->whereBetween('date_worked', [$start_date, $end_date])
                ->select('tblt_timesheet_hdr.link_subcon_id', 'tblt_timesheet_hdr.link_client_id', 'tblm_client.client_poc as client_name', 
                        DB::raw('SUM(tblt_timesheet_dtl.reg_ros_hours) AS work_total_hours'),
                        DB::raw('COUNT(DISTINCT(tblt_timesheet_hdr.link_subcon_id)) AS subcon_count'))
                ->groupBy('tblt_timesheet_hdr.link_client_id')
                ->offset($offset)
                ->paginate($limit);

        return $result;
    }

    private function generateTempInvoice($start_date, $end_date, $clients) 
    {
        $result = DB::connection('mysql2')->table('tblt_timesheet_hdr')
                ->join('tblt_timesheet_dtl','tblt_timesheet_dtl.link_tms_hdr','=','tblt_timesheet_hdr.id')
                ->join("tblm_client_subcon_pers", function($join) {
                    $join->on("tblm_client_subcon_pers.link_subcon_id","=","tblt_timesheet_hdr.link_subcon_id")
                            ->on("tblm_client_subcon_pers.link_client_id","=","tblt_timesheet_hdr.link_client_id");
                })
                ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
                ->whereIn('tblt_timesheet_hdr.link_client_id', $clients)
                ->whereBetween('date_worked', [$start_date, $end_date])
                ->select('tblt_timesheet_hdr.link_subcon_id', 'tblt_timesheet_hdr.link_client_id', 'tblm_client.client_poc as client_name', 
                        DB::raw('SUM(tblt_timesheet_dtl.reg_ros_hours) AS work_total_hours'),
                        DB::raw('COUNT(DISTINCT(tblt_timesheet_hdr.link_subcon_id)) AS subcon_count'))
                ->groupBy('tblt_timesheet_hdr.link_client_id')
                ->get();

        return $result;
    }

    public function timesheetPerClient(Request $request) 
    {
        $client = $request->query('client');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $limit = $request->query('limit');
        $page = $request->query('page');
        $offset = ($limit-1)*$page;

        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

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

        $result = DB::connection('mysql2')->table('tblt_timesheet_hdr')
                ->join('tblt_timesheet_dtl','tblt_timesheet_dtl.link_tms_hdr','=','tblt_timesheet_hdr.id')
                ->join($main_db.'.tblm_client_sub_contractor',$main_db.'.tblm_client_sub_contractor.id','=','tblt_timesheet_hdr.link_subcon_id')
                ->join($main_db.'.tblm_b_onboard_actreg_basic',$main_db.'.tblm_b_onboard_actreg_basic.reg_id','=',$main_db.'.tblm_client_sub_contractor.actreg_contractor_id')
                ->where('tblt_timesheet_hdr.link_client_id', '=', $client)
                ->whereBetween('date_worked', [$start_date, $end_date])
                ->select([$main_db.'.tblm_b_onboard_actreg_basic.reg_firstname',$main_db.'.tblm_b_onboard_actreg_basic.reg_lastname','tblt_timesheet_hdr.link_subcon_id','tblt_timesheet_dtl.date_worked','tblt_timesheet_dtl.work_time_in','tblt_timesheet_dtl.work_time_out','tblt_timesheet_dtl.reg_ros_hours as work_total_hours'])
                ->offset($offset)
                ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200);
    }

    public function temporaryInvoiceList(Request $request) 
    {
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        $limit = $request->query('limit');
        $page = $request->query('page');
        $offset = ($limit-1)*$page;

        $result = $this->getPaginatedInvoices($start_date, $end_date, $limit, $page, $offset);
        $allResults = $this->getGeneratedInvoices($start_date, $end_date);

        $totalAmount = 0;
        foreach ($allResults as $row) {
            $amount = floatval(preg_replace('/[^\d.]/', '', $row->invoice_amount));
            $totalAmount += $amount;
        }
        $totalAmount = number_format($totalAmount, 2);

        $totalPerPage = 0;
        foreach ($result as $row) {
            $amount = floatval(preg_replace('/[^\d.]/', '', $row->invoice_amount));
            $totalPerPage += $amount;
        }
        $totalPerPage = number_format($totalPerPage, 2);

        return response()->json([
            'success' => true,
            'data' => $result,
            'total_per_page' => $totalPerPage,
            'total' => $totalAmount
        ], 200);
    }

    private function getPaginatedInvoices($start_date, $end_date, $limit, $page, $offset) {
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        // $result = DB::connection('mysql2')->table('tblt_timesheet_hdr')
        //         ->join('tblt_timesheet_dtl','tblt_timesheet_dtl.link_tms_hdr','=','tblt_timesheet_hdr.id')
        //         ->join("tblm_client_subcon_pers", function($join) {
        //             $join->on("tblm_client_subcon_pers.link_subcon_id","=","tblt_timesheet_hdr.link_subcon_id")
        //                     ->on("tblm_client_subcon_pers.link_client_id","=","tblt_timesheet_hdr.link_client_id");
        //         })
        //         ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
        //         ->join('tblm_client_basic_rate','tblm_client_basic_rate.link_client_subcon_pers','=','tblm_client_subcon_pers.id')
        //         ->join('tblt_invoice_hdr','tblt_invoice_hdr.link_client_id','=','tblm_client.id')
        //         ->whereBetween('date_worked', [$start_date, $end_date])
        //         ->where('tblt_invoice_hdr.inv_period_from','=', $start_date)
        //         ->where('tblt_invoice_hdr.inv_period_to','=', $end_date)
        //         ->where('tblt_invoice_hdr.is_void','=', NULL)
        //         ->select('tblt_invoice_hdr.id', 'tblt_timesheet_hdr.link_subcon_id', 'tblt_timesheet_hdr.link_client_id', 'tblm_client.client_poc as client_name', DB::raw('FORMAT(tblt_invoice_hdr.net_amt, 2) as invoice_amount'), 
        //                 DB::raw("DATE_FORMAT(tblt_invoice_hdr.datecreated,'%d/%m/%Y') AS invoice_date"), 
        //                 DB::raw('SUM(tblt_timesheet_dtl.reg_ros_hours) AS work_total_hours'),
        //                 DB::raw('COUNT(DISTINCT(tblt_timesheet_hdr.link_subcon_id)) AS subcon_count'))
        //         ->groupBy('tblt_timesheet_hdr.link_client_id')
        //         ->offset($offset)
        //         ->paginate($limit);

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

        $result = DB::connection('mysql2')->table('tblt_invoice_hdr')
                ->join('tblt_invoice_dtl','tblt_invoice_dtl.link_inv_hdr','=','tblt_invoice_hdr.id')
                ->join('tblm_client','tblm_client.id','=','tblt_invoice_hdr.link_client_id')
                ->join($main_db.'.tblm_currency',$main_db.'.tblm_currency.id','=','tblm_client.client_currency')
                ->where('tblt_invoice_hdr.inv_period_from','=', $start_date)
                ->where('tblt_invoice_hdr.inv_period_to','=', $end_date)
                ->where('tblt_invoice_hdr.is_void','=', NULL)
                ->select(['tblt_invoice_hdr.id', 'tblm_client.client_poc as client_name', 'net_amt as invoice_amount', 'tblt_invoice_hdr.status_id', 'tblm_currency.code AS client_currency_code',
                        DB::raw("DATE_FORMAT(tblt_invoice_hdr.datecreated,'%d/%m/%Y') AS invoice_date"), 
                        DB::raw('SUM(tblt_invoice_dtl.hours_rendered) AS work_total_hours'),
                        DB::raw('COUNT(DISTINCT(tblt_invoice_dtl.particular)) AS subcon_count')])
                ->groupBy('tblt_invoice_dtl.link_inv_hdr')
                ->offset($offset)
                ->paginate($limit);

        return $result;
    }

    public function printInvoicePerClient(Request $request) {
        $this->clientInvoiceContent($request->query("id"), 'print');
    }

    public function sendInvoice(Request $request) {
        $invoiceHeader = DB::connection('mysql2')->table('tblt_invoice_hdr')
                            ->join('tblm_client','tblm_client.id','=','tblt_invoice_hdr.link_client_id')
                            ->where('tblt_invoice_hdr.id','=', $request->query("id"))
                            ->select('tblt_invoice_hdr.id as invoice_id', 'tblm_client.id as client_id', 'tblm_client.client_name', 'client_poc', 'client_addr_line1', 'client_email',
                                    'tblt_invoice_hdr.inv_date AS invoice_date', 'net_amt', 'inv_period_from', 'inv_period_to', 'tblt_invoice_hdr.status_id',
                                    'tblm_client.client_phone', 'tblm_client.client_email', 'tblm_client.client_ABN')
                            ->first();

        $invoice_period = date("F Y", strtotime($invoiceHeader->inv_period_from));

        $invoiceFile = $this->clientInvoiceContent($invoiceHeader->invoice_id, 'email');
        Mail::send('email.emailInvoice', ['invoice_id' => $request->id, 'client_name' => $invoiceHeader->client_poc, 'invoice_period' => $invoice_period], function($message) use($invoiceHeader, $invoiceFile){
            $message->to($invoiceHeader->client_email);
            $message->bcc('juliesse.rasco@remotestaff.com');
            // $message->to('juliesse.rasco@remotestaff.com');
            $message->subject('Client Invoice - '.$invoiceHeader->invoice_id);
            $message->attach($invoiceFile);
        });

        if ($invoiceHeader->status_id == 3) {
            DB::connection('mysql2')->table('tblt_invoice_resent')->insert(
                ['link_invoice_hdr_id' => $invoiceHeader->invoice_id, 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
            );
        }

        DB::connection('mysql2')->table('tblt_invoice_hdr')
              ->where('id', $invoiceHeader->invoice_id)
              ->update(['status_id' => 3]);

        return response()->json([
            'success' => true,
        ], 200);
    }

    private function clientInvoiceContent($invoice_id, $action) {
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

        $invoiceHeader = DB::connection('mysql2')->table('tblt_invoice_hdr')
                            ->join('tblm_client','tblm_client.id','=','tblt_invoice_hdr.link_client_id')
                            ->join($main_db.'.tblm_currency',$main_db.'.tblm_currency.id','=','tblm_client.client_currency')
                            ->where('tblt_invoice_hdr.id','=', $invoice_id)
                            ->select('tblm_client.id', 'tblm_client.client_name', 'client_poc', 'client_addr_line1', 
                                    'tblt_invoice_hdr.inv_date AS invoice_date', 'tblt_invoice_hdr.due_date AS due_date', 'net_amt', 'inv_period_from', 'inv_period_to', 
                                    'tblm_client.client_phone', 'tblm_client.client_email', 'tblm_client.client_ABN', 'tblm_client.client_currency',
                                    'tblm_currency.code AS client_currency_code')
                            ->first();

        $getReferenceRate = DB::connection('mysql2')->table('tblm_forex_for_client_invoice')
            ->join($main_db.'.tblm_currency', $main_db.'.tblm_currency.id','=','tblm_forex_for_client_invoice.currency_id')
            ->where($main_db.'.tblm_currency.id', '=', $invoiceHeader->client_currency)
            ->where('tblm_forex_for_client_invoice.isActive', '=', 1)
            ->where('tblm_forex_for_client_invoice.effective_month_year', '=', $invoiceHeader->inv_period_from)
            ->select(['tblm_forex_for_client_invoice.rate'])
            ->first();

        $invoiceDetails = DB::connection('mysql2')->table('tblt_invoice_dtl')
                            ->join('tblt_invoice_hdr','tblt_invoice_hdr.id','=','tblt_invoice_dtl.link_inv_hdr')
                            ->where('tblt_invoice_dtl.link_inv_hdr', '=', $invoice_id)
                            ->select(['particular', 'hours_rendered', 'rate_per_hour', 'billable_amt'])
                            ->get();

        $invoiceAddtlDetails = DB::connection('mysql2')->table('tblt_invoice_dtl_2_add')
                            ->where('link_invoice_hdr_id', '=', $invoice_id)
                            ->where('tblt_invoice_dtl_2_add.is_void','=', NULL)
                            ->where('tblt_invoice_dtl_2_add.particular', 'not like', '%gst%')
                            ->select(['particular', 'amount_add_on', 'total_hours', 'hourly_rate'])
                            ->get();

        $getGst = DB::connection('mysql2')->table('tblt_invoice_dtl_2_add')
                        ->join('tblt_invoice_hdr','tblt_invoice_hdr.id','=','tblt_invoice_dtl_2_add.link_invoice_hdr_id')
                        ->join($main_db.'.tblm_invoice_item_type', $main_db.'.tblm_invoice_item_type.id','=','tblt_invoice_dtl_2_add.link_invoice_item_type_id')
                        ->where('link_invoice_hdr_id', '=', $invoice_id)
                        ->where($main_db.'.tblm_invoice_item_type.description', 'like', '%gst%')
                        ->where('tblt_invoice_dtl_2_add.is_void', '=', NULL)
                        ->select([$main_db.'.tblm_invoice_item_type.percentage'])
                        ->first();

        $subTotalAmount = 0;
        $totalAmount = 0;
        $gst = 0;
        foreach ($invoiceDetails as $key => $row) {
            $subTotalAmount += $row->billable_amt;
        }

        foreach ($invoiceAddtlDetails as $key => $row) {
            $subTotalAmount += $row->amount_add_on;
        }

        if(isset($getGst->percentage) && $getGst->percentage != '' && !empty($getGst->percentage)) {
            $totalAmount = $subTotalAmount - ($subTotalAmount * ($getGst->percentage/100));
            $gst = $subTotalAmount * ($getGst->percentage/100);
        }
        else {
            $totalAmount = $subTotalAmount;
        }

        // var_dump($invoiceDetails);
        // return true;
        // exit;
        header('Content-Type: application/pdf');
        header("Content-Disposition:attachment;filename='tests.pdf'");
        
        $pdf = new FPDF();
        $pdf->AddPage('P', 'A4');
        $pdf->SetMargins(10, 10); 
        $pdf->SetAutoPageBreak(true, 10);

        // $fileName = '/rs-logo.png';
        // $publicPath = public_path();
        // $rsLogo = $publicPath.$fileName;        
        // $pdf->Image($rsLogo, 60, 30, 90, 0, 'PNG');

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(190,6,'Think Innovations Pty Ltd', 0, 1, 'C');
        $pdf->Cell(190,6,'ABN 37 094 364 511', 0, 1, 'C');
        $pdf->Cell(190,6,'Level 35, Tower One Barangaroo', 0, 1, 'C');
        $pdf->Cell(190,6,'International Towers Sydney, 60 Barangaroo Avenue', 0, 1, 'C');
        $pdf->Cell(190,6,'Sydney NSW, 2000', 0, 1, 'C');
        $pdf->SetFont('Arial','B', 14);
        $pdf->Ln();
        $pdf->Cell(190,10,'TAX INVOICE NUMBER '.$invoice_id, 0, 1, 'C');
        $pdf->Ln();

        $pdf->SetFont('Arial','B', 12);
        $pdf->Cell(90,8,'BILL TO', 'B', 0, 'L');
        $pdf->Cell(10,8,'', '', 0, 'L');
        $pdf->Cell(90,8,'INVOICE DETAILS', 'B', 1, 'L');
        $pdf->Cell(190,5,'', 0, 1, 'C');

        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(90,5,'CLIENT ID', 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5,'INVOICE DATE', 0, 1, 'L');

        $pdf->SetFont('Arial','', 8);
        $pdf->Cell(90,5, $invoiceHeader->id, 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $invoice_date=date_create($invoiceHeader->invoice_date);
        $pdf->Cell(90,5, date_format($invoice_date,"F d, Y g:i A"), 0, 1, 'L');

        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(90,5,'NAME', 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5,'PAYMENT DUE DATE', 0, 1, 'L');

        $pdf->SetFont('Arial','', 8);
        $pdf->Cell(90,5, $invoiceHeader->client_poc, 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $due_date=date_create($invoiceHeader->due_date);
        $pdf->Cell(90,5, (isset($invoiceHeader->due_date)) ? date_format($due_date,"F d, Y") : '', 0, 1, 'L');

        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(90,5,'EMAIL', 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5,'PHONE', 0, 1, 'L');

        $pdf->SetFont('Arial','', 8);
        $pdf->Cell(90,5, $invoiceHeader->client_email, 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5, $invoiceHeader->client_phone, 0, 1, 'L');

        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(90,5,'COMPANY', 0, 0, 'L');
        $pdf->Cell(10,5, '', '', 0, 'L');
        $pdf->Cell(90,5,'INVOICE STATUS', 0, 1, 'L');

        $pdf->SetFont('Arial','', 8);
        $pdf->Cell(90,5, $invoiceHeader->client_name, 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5, '', 0, 1, 'L');

        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(90,5,'ABN', 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5,'INVOICE CURRENCY', 0, 1, 'L');

        $pdf->SetFont('Arial','', 8);
        $pdf->Cell(90,5, $invoiceHeader->client_ABN, 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5, $invoiceHeader->client_currency_code, 0, 1, 'L');

        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(90,5,'COMPANY ADDRESS', 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5,'AMOUNT OUTSTANDING', 0, 1, 'L');

        $pdf->SetFont('Arial','', 8);
        $pdf->Cell(90,5, $invoiceHeader->client_addr_line1, 0, 0, 'L');
        $pdf->Cell(10,5,'', '', 0, 'L');
        $pdf->Cell(90,5, '$'.number_format($totalAmount, 2), 0, 1, 'L');

        if (isset($getReferenceRate->rate)) {
            $pdf->SetFont('Arial','B', 8);
            $pdf->Cell(90,5,'REFERENCE FX', 0, 1, 'L');
    
            $pdf->SetFont('Arial','', 8);
            $pdf->Cell(90,5, $getReferenceRate->rate, 0, 1, 'L');
        }

        // $pdf->SetFont('Arial','B', 8);
        // $pdf->Cell(190,5,'PHONE', 0, 1, 'L');

        // $pdf->SetFont('Arial','', 8);
        // $pdf->Cell(190,5, $invoiceHeader->client_phone, 0, 1, 'L');

        $pdf->Cell(190,5,'', 'B', 1, 'C');
        $pdf->Ln();

        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(190,5,'INVOICE SUMMARY', 0, 1, 'C');
        $pdf->Ln();

        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(15,8,'Item No.', 1, 0, 'C');
        $pdf->Cell(35,8,'Cover Date', 1, 0, 'C');
        $pdf->Cell(65,8,'Description', 1, 0, 'C');
        $pdf->Cell(30,8,'No. of Hours', 1, 0, 'C');
        $pdf->Cell(20,8,'Hourly Rate', 1, 0, 'C');
        $pdf->Cell(20,8,'Amount', 1, 1, 'C');

        $pdf->SetFont('Arial','', 8);
        $item = 1;
        foreach ($invoiceDetails as $key => $row) {
            // if ($key < 10) {
                $pdf->Cell(15,8, $item , 1, 0, 'C');
                $pdf->Cell(35,8, $invoiceHeader->inv_period_from.' - '.$invoiceHeader->inv_period_to , 1, 0, 'L');
                $pdf->Cell(65,8, $row->particular, 1, 0, 'L');
                $pdf->Cell(30,8, $row->hours_rendered , 1, 0, 'C');
                $pdf->Cell(20,8, '$'. number_format($row->rate_per_hour, 2) , 1, 0, 'R');
                $pdf->Cell(20,8, '$'. number_format($row->billable_amt, 2) , 1, 1, 'R');

                $item+=1;
            // }
        }

        foreach ($invoiceAddtlDetails as $key => $row) {
            $pdf->Cell(15,8, $item , 1, 0, 'C');
            $pdf->Cell(35,8, $invoiceHeader->inv_period_from.' - '.$invoiceHeader->inv_period_to , 1, 0, 'L');
            $pdf->Cell(65,8, $row->particular, 1, 0, 'L');
            $pdf->Cell(30,8, $row->total_hours , 1, 0, 'C');
            $pdf->Cell(20,8, '$'. number_format($row->hourly_rate, 2) , 1, 0, 'R');
            $pdf->Cell(20,8, '$'. number_format($row->amount_add_on, 2) , 1, 1, 'R');

            $item+=1;
        }

        $pdf->Cell(165,8, 'AMOUNT' , 1, 0, 'R');
        $pdf->Cell(20,8, '$'. number_format($subTotalAmount, 2), 1, 1, 'R');

        $pdf->Cell(165,8, 'GST' , 1, 0, 'R');
        $pdf->Cell(20,8, '$'. number_format($gst, 2), 1, 1, 'R');

        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(165,8, 'TOTAL AMOUNT' , 1, 0, 'R');
        $pdf->Cell(20,8, '$'. number_format($totalAmount, 2), 1, 1, 'R');

        $pdf->Ln();
        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(190,5,'HOW TO PAY', 0, 1, 'C');

        $pdf->Ln();
        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(190,5,'Online using Credit / Debit Card for all currencies.', 0, 1, 'L');
        $pdf->SetFont('Arial','', 8);
        $pdf->Cell(190,5,'        Click HERE to pay online using Visa, Mastercard or Amex. Merchant facility fee applies for credit card (AMEX/ Visa / MasterCard: 1.3%', 0, 1, 'L');
        $pdf->Cell(190,5,'for AUD ; 2.5% for other currencies).', 0, 1, 'L');

        $pdf->Ln();
        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(190,5,'Via Phone with Credit / Debit Card for all currencies.', 0, 1, 'L');
        $pdf->SetFont('Arial','', 8);
        $pdf->Cell(190,5,'       Call +61 2 7201 9698 to pay using a credit card. Merchant facility fee applies for credit cards (AMEX/ Visa / MasterCard: 1.3% for AUD ;', 0, 1, 'L');
        $pdf->Cell(190,5,'2.5% for other currencies).', 0, 1, 'L');

        if ($invoiceHeader->client_currency_code == 'AUD') {
            $pdf->Ln();
            $pdf->SetFont('Arial','B', 8);
            $pdf->Cell(190,5,'Pay ID', 0, 1, 'L');
            $pdf->Cell(35,5,'       Remote Staff Pay ID:', 0, 0, 'L');
            $pdf->SetFont('Arial','', 8);
            $pdf->Cell(50,5,'admin@remotestaff.com.au', 0, 1, 'L');
        }

        $pdf->Ln();
        $pdf->SetFont('Arial','B', 8);
        $pdf->Cell(190,5,'Bank Transfer', 0, 1, 'L');
        if ($invoiceHeader->client_currency_code == 'GBP') {
            $pdf->Cell(100,5,'       United Kingdom (Pounds)', 0, 1, 'L');
            $pdf->SetFont('Arial','', 8);
            $pdf->Cell(100,5,'       Bank Name : HSBC London-Notting Hill Branch', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Name: Think Innovations Pty Ltd', 0, 1, 'L');
            $pdf->Cell(100,5,'       Sort Code: 40-05-09', 0, 1, 'L');
            $pdf->Cell(100,5,'       Acc: 61-50-63-23', 0, 1, 'L');
            $pdf->Cell(100,5,'       Swift Code: HBUKGB4B', 0, 1, 'L');
            $pdf->Cell(100,5,'       IBAN Number: GB91HBUK40050961506323', 0, 1, 'L');
            $pdf->Ln();
            $pdf->Cell(100,5,'       Bank Name : NAB Bondi Junction Branch', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Name: Think Innovations Pty Ltd', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Number: THINNGBP02', 0, 1, 'L');
            $pdf->Cell(100,5,'       Swift Code: NATAAU3302S', 0, 1, 'L');
            $pdf->Cell(100,5,'       IBAN Number: GB91HBUK40050961506323', 0, 1, 'L');
            $pdf->Ln();
            $pdf->Cell(100,5,'       Bank Address: NAB Branch at World Square', 0, 1, 'L');
            $pdf->Cell(100,5,'       Level 15, 680 George St, Sydney NSW 2000 ', 0, 1, 'L');
        }
        else if ($invoiceHeader->client_currency_code == 'NZD') {
            $pdf->Cell(100,5,'       New Zealand (Dollar)', 0, 1, 'L');
            $pdf->SetFont('Arial','', 8);
            $pdf->Cell(100,5,'       Bank Name : NAB Bondi Junction Branch', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Name: Think Innovations Pty Ltd', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Number: THINNNZD01', 0, 1, 'L');
            $pdf->Cell(100,5,'       Swift Code: NATAAU3302S', 0, 1, 'L');
            $pdf->Ln();
            $pdf->Cell(100,5,'       Bank Address: NAB Branch at World Square', 0, 1, 'L');
            $pdf->Cell(100,5,'       Level 15, 680 George St, Sydney NSW 2000 ', 0, 1, 'L');
        }
        else if ($invoiceHeader->client_currency_code == 'USD') {
            $pdf->Cell(100,5,'       US (Dollar)', 0, 1, 'L');
            $pdf->SetFont('Arial','', 8);
            $pdf->Cell(100,5,'       Bank Name : NAB Bondi Junction Branch', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Name: Think Innovations Pty Ltd', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Number: THINNUSDO1', 0, 1, 'L');
            $pdf->Cell(100,5,'       Swift Code: NATAAU3302S', 0, 1, 'L');
            $pdf->Ln();
            $pdf->Cell(100,5,'       Bank Address: NAB Branch at World Square', 0, 1, 'L');
            $pdf->Cell(100,5,'       Level 15, 680 George St, Sydney NSW 2000 ', 0, 1, 'L');
        }
        else if ($invoiceHeader->client_currency_code == 'CAD') {
            $pdf->Cell(100,5,'       Canadian (Dollar)', 0, 1, 'L');
            $pdf->SetFont('Arial','', 8);
            $pdf->Cell(100,5,'       Bank Name : NAB Bondi Junction Branch', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Name: Think Innovations Pty Ltd. - trading as Remote Staf', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Number: THINNCAD01', 0, 1, 'L');
            $pdf->Cell(100,5,'       Swift Code: NATAAU3302S', 0, 1, 'L');
            $pdf->Ln();
            $pdf->Cell(100,5,'       Bank Address: NAB Branch at World Square', 0, 1, 'L');
            $pdf->Cell(100,5,'       Level 15, 680 George St, Sydney NSW 2000 ', 0, 1, 'L');
        }
        else {
            $pdf->Cell(100,5,'       Australia (Dollar)', 0, 1, 'L');
            $pdf->SetFont('Arial','', 8);
            $pdf->Cell(100,5,'       Bank Name : NAB Bondi Junction Branch', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Name: Think Innovations Pty Ltd', 0, 1, 'L');
            $pdf->Cell(100,5,'       BSB: 082 140', 0, 1, 'L');
            $pdf->Cell(100,5,'       Account Number: 49 058 9267', 0, 1, 'L');
            $pdf->Cell(100,5,'       Swift Code: NATAAU3303M', 0, 1, 'L');
        }
        
        if ($invoiceHeader->client_currency_code == 'AUD') {
            $pdf->Ln();
            $pdf->SetFont('Arial','B', 8);
            $pdf->Cell(190,5,'Paypal', 0, 1, 'L');
            $pdf->SetFont('Arial','', 8);
            $pdf->Cell(190,5,'       Pay To : Accounts@remotestaff.com.au . PayPal fees range from 1.1% - 2.4% of your invoice amount. Fees will be charged on your next', 0, 1, 'L');
            $pdf->Cell(190,5,"month's invoice.", 0, 1, 'L');
        }

        if ($action == 'email') {
            $fileName = 'Invoice'.time().'.pdf';
            $filePath = public_path($fileName);
            $pdf->Output('F', $filePath);
            return $filePath;
        }
        else {
            $pdf->Output();
        }
    }

    public function exportInvoicesToPDF(Request $request) {
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $result = $this->getGeneratedInvoices($start_date, $end_date);

        $totalAmount = 0;
        foreach ($result as $row) {
            $amount = floatval(preg_replace('/[^\d.]/', '', $row->invoice_amount));
            $totalAmount += $amount;
        }
        $totalAmount = number_format($totalAmount, 2);

        header('Content-Type: application/pdf');
        header("Content-Disposition:attachment;filename='tests.pdf'");
        
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B', 12);
        $pdf->Cell(190,10,'Remote Staff Inc.', 0, 1, 'C');
        $pdf->Cell(190,10,'Summary of Invoices', 0, 1, 'C');
        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(190,10,'Generated as of '.date("F d, Y"), 0, 1, 'C');
        $pdf->Ln();

        $pdf->SetFont('Arial','', 10);
        $pdf->Cell(30,10,'Invoice No.', 1, 0, 'C');
        $pdf->Cell(30,10,'Invoice Date', 1, 0, 'C');
        $pdf->Cell(70,10,'Client Name', 1, 0, 'C');
        $pdf->Cell(30,10,'No. of Subcon', 1, 0, 'C');
        $pdf->Cell(30,10,'Amount', 1, 1, 'C');

        foreach ($result as $row) {
            $pdf->Cell(30,10, $row->id , 1, 0, 'C');
            $pdf->Cell(30,10, $row->invoice_date , 1, 0, 'C');
            $pdf->Cell(70,10, $row->client_name , 1, 0, 'L');
            $pdf->Cell(30,10, $row->subcon_count , 1, 0, 'C');
            $pdf->Cell(30,10, 'AUD '. $row->invoice_amount , 1, 1, 'R');
        }

        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(160,10,'Total Net Invoice Amount', 1, 0, 'R');
        $pdf->Cell(30,10,'AUD '. $totalAmount, 1, 1, 'R');

        $pdf->Output();
    }

    public function exportInvoicesToCSV(Request $request) {
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $result = $this->getGeneratedInvoices($start_date, $end_date);

        $totalAmount = 0;
        foreach ($result as $row) {
            $amount = floatval(preg_replace('/[^\d.]/', '', $row->invoice_amount));
            $totalAmount += $amount;
        }
        $totalAmount = number_format($totalAmount, 2);

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getActiveSheet()->getStyle('A1:A3')->getFont()->setBold( true );
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A1', 'Remote Staff Inc');
        $sheet->setCellValue('A2', 'Summary of Invoices');
        $sheet->setCellValue('A3', 'Generated as of '.date("F d, Y"));
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');

        $spreadsheet->getActiveSheet()->getStyle('A5:E5')->getFont()->setBold( true );
        $header_row_array = ['Invoice No', 'Invoice Date', 'Client Name', 'No. of Subcon', 'Amount'];
        $spreadsheet->getActiveSheet()->fromArray( $header_row_array, NULL, 'A5' );

        $count = 6;
        foreach($result as $row){
            $spreadsheet->getActiveSheet()->fromArray(array($row->id, $row->invoice_date, $row->client_name, $row->subcon_count, 'AUD '.$row->invoice_amount), null, 'A'.$count);
            $count++;
        }

        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getActiveSheet()->getStyle('A'.$count.':E'.$count)->getFont()->setBold( true );
        $sheet->setCellValue('A'.$count, 'Total Net Invoice Amount');
        $sheet->getStyle('A'.$count)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->mergeCells('A'.$count.':D'.$count);
        $sheet->setCellValue('E'.$count, 'AUD '.$totalAmount);    
        
        $fileName = 'Invoice_Summary'.time().'.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($fileName);

        $filePath = public_path($fileName);
        return response()->download($filePath);
    }

    public function exportInvoicesToHTML(Request $request) {
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $result = $this->getGeneratedInvoices($start_date, $end_date);

        $totalAmount = 0;
        foreach ($result as $row) {
            $amount = floatval(preg_replace('/[^\d.]/', '', $row->invoice_amount));
            $totalAmount += $amount;
        }
        $totalAmount = number_format($totalAmount, 2);

        echo '<html>
                <div align="center" style="margin:20px 0">
                    <p style="margin:5px 0; font-weight: bold">Remote Staff Inc.</p>
                    <p style="margin:5px 0; font-weight: bold">Summary of Invoices</p>
                    <p style="margin:5px 0; font-weight: bold">Generated as of '.date("F d, Y").'</p>
                </div>
                <div align="center">
                    <table style="width:800px; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="border:1px solid">Invoice ID</th>
                                <th style="border:1px solid">Invoice Date</th>
                                <th style="border:1px solid">Client Name</th>
                                <th style="border:1px solid">No. of Subcon</th>
                                <th style="border:1px solid">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>';
                            foreach($result as $row){
                                echo '<tr>
                                    <td align="center" style="border:1px solid">'.$row->id.'</td>
                                    <td align="center" style="border:1px solid">'.$row->invoice_date.'</td>
                                    <td style="border:1px solid">'.$row->client_name.'</td>
                                    <td align="center" style="border:1px solid">'.$row->subcon_count.'</td>
                                    <td align="right" style="border:1px solid">AUD '.$row->invoice_amount.'</td>
                                </tr>';
                            }
                        echo '</tbody>
                        <tfoot>
                            <tr>
                                <td colspan = "4" align="right" style="border:1px solid; font-weight: bold">Total Net Invoice Amount</td>
                                <td align="right" style="border:1px solid; font-weight: bold">AUD '.$totalAmount.'</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </html>';

        header('Content-type: text/html');
        header("Content-Disposition:attachment");
    }

    private function getGeneratedInvoices($start_date, $end_date) {
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        // $result = DB::connection('mysql2')->table('tblt_timesheet_hdr')
        //         ->join('tblt_timesheet_dtl','tblt_timesheet_dtl.link_tms_hdr','=','tblt_timesheet_hdr.id')
        //         ->join("tblm_client_subcon_pers", function($join) {
        //             $join->on("tblm_client_subcon_pers.link_subcon_id","=","tblt_timesheet_hdr.link_subcon_id")
        //                     ->on("tblm_client_subcon_pers.link_client_id","=","tblt_timesheet_hdr.link_client_id");
        //         })
        //         ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
        //         ->join('tblm_client_basic_rate','tblm_client_basic_rate.link_client_subcon_pers','=','tblm_client_subcon_pers.id')
        //         ->join('tblt_invoice_hdr','tblt_invoice_hdr.link_client_id','=','tblm_client.id')
        //         ->whereBetween('date_worked', [$start_date, $end_date])
        //         ->where('tblt_invoice_hdr.inv_period_from','=', $start_date)
        //         ->where('tblt_invoice_hdr.inv_period_to','=', $end_date)
        //         ->where('tblt_invoice_hdr.is_void','=', NULL)
        //         ->select('tblt_invoice_hdr.id', 'tblt_timesheet_hdr.link_subcon_id', 'tblt_timesheet_hdr.link_client_id', 'tblm_client.client_poc as client_name', DB::raw('FORMAT(tblt_invoice_hdr.net_amt, 2) as invoice_amount'), 
        //                 DB::raw("DATE_FORMAT(tblt_invoice_hdr.datecreated,'%d/%m/%Y') AS invoice_date"), 
        //                 DB::raw('SUM(tblt_timesheet_dtl.reg_ros_hours) AS work_total_hours'),
        //                 DB::raw('COUNT(DISTINCT(tblt_timesheet_hdr.link_subcon_id)) AS subcon_count'))
        //         ->groupBy('tblt_timesheet_hdr.link_client_id')
        //         ->get();

        $result = DB::connection('mysql2')->table('tblt_invoice_hdr')
                ->join('tblt_invoice_dtl','tblt_invoice_dtl.link_inv_hdr','=','tblt_invoice_hdr.id')
                ->join('tblm_client','tblm_client.id','=','tblt_invoice_hdr.link_client_id')
                ->where('tblt_invoice_hdr.inv_period_from','=', $start_date)
                ->where('tblt_invoice_hdr.inv_period_to','=', $end_date)
                ->where('tblt_invoice_hdr.is_void','=', NULL)
                ->select('tblt_invoice_hdr.id', 'tblm_client.client_poc as client_name', 'net_amt as invoice_amount', 
                        DB::raw("DATE_FORMAT(tblt_invoice_hdr.datecreated,'%d/%m/%Y') AS invoice_date"), 
                        DB::raw('SUM(tblt_invoice_dtl.hours_rendered) AS work_total_hours'),
                        DB::raw('COUNT(DISTINCT(tblt_invoice_dtl.particular)) AS subcon_count'))
                ->groupBy('tblt_invoice_dtl.link_inv_hdr')
                ->get();

        return $result;
    }

    private function calculateWorkedHours($start_date, $end_date, $client, $process) 
    {
        $result = DB::connection('mysql2')->table('tblt_timesheet_hdr')
                ->join('tblt_timesheet_dtl','tblt_timesheet_dtl.link_tms_hdr','=','tblt_timesheet_hdr.id')
                ->join("tblm_client_subcon_pers", function($join) {
                    $join->on("tblm_client_subcon_pers.link_subcon_id","=","tblt_timesheet_hdr.link_subcon_id")
                            ->on("tblm_client_subcon_pers.link_client_id","=","tblt_timesheet_hdr.link_client_id");
                })
                ->join('tblm_client','tblm_client.id','=','tblm_client_subcon_pers.link_client_id')
                ->where('tblt_timesheet_hdr.link_client_id', '=', $client)
                ->whereBetween('date_worked', [$start_date, $end_date])
                ->select('tblt_timesheet_hdr.link_subcon_id', 'tblt_timesheet_hdr.link_client_id', 'tblm_client.client_name', DB::raw('SUM(tblt_timesheet_dtl.reg_ros_hours) AS work_total_hours'))
                ->when($process == 'get_breakdown_per_subcon', function ($q) {
                    return $q->groupBy('tblt_timesheet_hdr.link_subcon_id');
                })
                ->get();

        return $result;
    }

    public function generateTimesheet(Request $request) {
        $date = $request->query('month_year');

        $start_date = date("Y-m-d", strtotime($date));
        $getTimesheet = DB::connection('mysql2')->table('tblt_timesheet_dtl')
                ->where('date_worked', '=', $start_date)
                ->select('id')
                ->first();

        if (isset($getTimesheet->id) || !empty($getTimesheet->id)) {
            return response()->json([
                'success' => false,
                'message' => 'timesheet generated'
            ], 200);
        }

        $result = DB::table('tblm_client_sub_contractor')
                // ->whereIn('status', array('active', 'suspended'))
                ->select('id', 'mon_number_hrs', 'tue_number_hrs', 'wed_number_hrs', 'thu_number_hrs', 'fri_number_hrs', 'sat_number_hrs', 'sun_number_hrs')
                ->get();

        $days = (object)[];
        $date = date('F Y', strtotime($date));//Current Month Year
        // while (strtotime($date) <= strtotime(date('Y-m') . '-' . date('t', strtotime($date)))) {
        //     $day = date('l', strtotime($date));//Day name
        //     $date = date('Y-m-d', strtotime($date));
        //     $days->date[] = $date;
        //     $days->day[] = $day;

        //     $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));//Adds 1 day onto current date
        // }
        $no_of_days = date('t', strtotime($date));
        for ($i=1; $i <= $no_of_days; $i++) {
            $day = date('l', strtotime($date));//Day name
            $date = date('Y-m-d', strtotime($date));
            $days->date[] = $date;
            $days->day[] = $day;

            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));//Adds 1 day onto current date
        }

        foreach ($result as $row) {
            $getClientId = DB::connection('mysql2')->table('tblm_client_subcon_pers')
                ->where('link_subcon_id', '=', $row->id)
                ->select('link_client_id')
                ->first();

            if (isset($getClientId->link_client_id)) {
                $timesheetHeader = DB::connection('mysql2')->table('tblt_timesheet_hdr')->insertGetId(
                    ['link_client_id' => $getClientId->link_client_id, 'link_subcon_id' => $row->id, 'month_year' => $start_date." 00:00:00", 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
                );

                foreach ($days->day as $key => $data) {
                    if ($data == "Monday") {
                        $work_hours = $row->mon_number_hrs;
                    }
                    elseif ($data == "Tuesday") {
                        $work_hours = $row->tue_number_hrs;
                    }
                    elseif ($data == "Wednesday") {
                        $work_hours = $row->wed_number_hrs;
                    }
                    elseif ($data == "Thursday") {
                        $work_hours = $row->thu_number_hrs;
                    }
                    elseif ($data == "Friday") {
                        $work_hours = $row->fri_number_hrs;
                    }
                    elseif ($data == "Saturday") {
                        $work_hours = $row->sat_number_hrs;
                    }
                    elseif ($data == "Sunday") {
                        $work_hours = $row->sun_number_hrs;
                    }

                    $work_hours = isset($work_hours) ? $work_hours : 0;

                    $timesheetDtl = DB::connection('mysql2')->table('tblt_timesheet_dtl')->insertGetId(
                        ['link_tms_hdr' => $timesheetHeader, 'date_worked' => $days->date[$key], 'reg_ros_hours' => $work_hours, 'createdby' => auth()->user()->id, 'datecreated' => date("Y-m-d h:i:s")]
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200);
    }

    public function getUnpaidInvoices(Request $request) {
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

        $result = DB::connection('mysql2')->table('tblt_invoice_hdr')
                ->join('tblt_invoice_dtl','tblt_invoice_dtl.link_inv_hdr','=','tblt_invoice_hdr.id')
                ->join('tblm_client','tblm_client.id','=','tblt_invoice_hdr.link_client_id')
                ->join($main_db.'.tblm_currency',$main_db.'.tblm_currency.id','=','tblm_client.client_currency')
                ->where('tblt_invoice_hdr.status_id','=', 4)
                ->where('tblt_invoice_hdr.is_void','=', NULL)
                ->select(['tblt_invoice_hdr.id', 'net_amt as invoice_amount',
                        DB::raw("DATE_FORMAT(tblt_invoice_hdr.inv_date,'%d/%m/%Y') AS invoice_date"),
                        DB::raw("DATE_FORMAT(tblt_invoice_hdr.due_date,'%d/%m/%Y') AS due_date")])
                ->groupBy('tblt_invoice_dtl.link_inv_hdr')
                ->paginate($request->limit ? $request->limit : ForexRate::count());

        return response()->json([
            'success' => true,
            'data' => $result
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
    public function update()
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete()
    {
        
    }
}
