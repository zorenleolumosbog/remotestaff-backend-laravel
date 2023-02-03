<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Users\OnboardProfileBasicInfo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RegistrantReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verified(Request $request, $id)
    {
        $registrants = tap(OnboardProfileBasicInfo::
        join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->where('email', 'LIKE', "{$request->search}%");
                $query->orWhere('reg_firstname', 'LIKE', "{$request->search}%");
                $query->orWhere('reg_lastname', 'LIKE', "{$request->search}%");
            });
        })
        // ->whereDate('date_submitted', Carbon::now()->format('Y-m-d'))
        ->whereNotNull('is_verified')
        ->where('registrant_type', $id)
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : OnboardProfileBasicInfo::count()))
        ->makeHidden(['password', 'email_passwd_conf']);

        if( $registrants->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $registrants,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function unverified(Request $request, $id)
    {
        $registrants = tap(OnboardProfileBasicInfo::
        join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->where('email', 'LIKE', "{$request->search}%");
                $query->orWhere('reg_firstname', 'LIKE', "{$request->search}%");
                $query->orWhere('reg_lastname', 'LIKE', "{$request->search}%");
            });
        })
        // ->whereDate('date_submitted', Carbon::now()->format('Y-m-d'))
        ->whereNull('is_verified')
        ->whereNull('is_expired')
        ->where('registrant_type', $id)
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : OnboardProfileBasicInfo::count()))
        ->makeHidden(['password', 'email_passwd_conf']);

        if( $registrants->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $registrants,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function expired(Request $request, $id)
    {
        $registrants = tap(OnboardProfileBasicInfo::
        join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->where('email', 'LIKE', "{$request->search}%");
                $query->orWhere('reg_firstname', 'LIKE', "{$request->search}%");
                $query->orWhere('reg_lastname', 'LIKE', "{$request->search}%");
            });
        })
        // ->whereDate('date_submitted', Carbon::now()->format('Y-m-d'))
        ->whereNull('is_verified')
        ->whereNotNull('is_expired')
        ->where('registrant_type', $id)
        ->orderBy('id', 'desc')
        ->paginate($request->limit ? $request->limit : OnboardProfileBasicInfo::count()))
        ->makeHidden(['password', 'email_passwd_conf']);

        if( $registrants->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $registrants,
        ], 200);
    }
}
