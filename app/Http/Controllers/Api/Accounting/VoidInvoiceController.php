<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Accounting\ClientInvoiceHeader;
use Carbon\Carbon;

class VoidInvoiceController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show( $id )
    {

        $client_invoice_header = ClientInvoiceHeader::where('tblt_invoice_hdr.id', '=', $id)
        ->select('tblt_invoice_hdr.*', 'tblm_client.client_poc')
        ->leftjoin('tblm_client', 'tblt_invoice_hdr.link_client_id', '=', 'tblm_client.id')
        ->whereNull('is_void')
        ->get();

        if($client_invoice_header->count() == 0) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $client_invoice_header,
        ], 200);
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
            'void_reason' => 'required:mysql2.tblm_client',
        ],
        [
            'void_reason.required' => 'The Void Reason is requried.',
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        ClientInvoiceHeader::where('id', $request->id)->update(
            [
                'is_void' => 1,
                'void_reason' => $request->void_reason,
                'voidedby' => auth()->user()->id,
                'datemodified' => Carbon::now()
            ]
        );

        $client_invoice_header = ClientInvoiceHeader::where('id', $request->id)->first();
        $client_invoice_header->invoiceHeaderSubDetails()->update(
            [
                'is_void' => 1,
                'void_reason' => $request->void_reason,
                'voidedby' => auth()->user()->id,
                'datemodified' => Carbon::now()
            ]
        );

        return response()->json([
            'success' => true,
            'data' => ClientInvoiceHeader::find($request->id),
        ], 200);
    }
}
