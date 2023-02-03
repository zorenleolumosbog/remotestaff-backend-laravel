<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Client;
use App\Models\Admin\ClientRemoteContractor;
use App\Models\Admin\ClientRemoteContractorPersonnel;
use App\Models\Admin\Registrant;
use App\Models\Admin\RegistrantType;
use App\Models\Users\OnboardProfileBasicInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegistrantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getJobseeker(Request $request)
    {
        $registrants = tap(OnboardProfileBasicInfo::
        selectRaw(
            'tblm_a_onboard_prereg.id,
            CONCAT_WS(" ", tblm_b_onboard_actreg_basic.reg_firstname, tblm_b_onboard_actreg_basic.reg_lastname) AS complete_name'
        )
        ->join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->where('email', 'LIKE', "{$request->search}%");
                $query->orWhere(DB::raw('CONCAT_WS(" ", reg_firstname, reg_lastname)'), 'LIKE', "{$request->search}%");
            });
        })
        ->whereNotNull('is_verified')
        ->whereNotNull('reg_firstname')
        ->where('reg_firstname', '<>', '')
        ->where('registrant_type', 1)
        ->orderBy('reg_firstname', 'asc')
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
    public function getRemoteContractor(Request $request)
    {
        $registrants = tap(OnboardProfileBasicInfo::
        select('tblm_b_onboard_actreg_basic.reg_link_preregid')
        ->selectRaw(
            'tblm_a_onboard_prereg.id,
            tblm_b_onboard_actreg_basic.registrant_type,
            CONCAT_WS(" ", tblm_b_onboard_actreg_basic.reg_firstname, tblm_b_onboard_actreg_basic.reg_lastname) AS complete_name'
        )
        ->join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->where('tblm_a_onboard_prereg.id', 'LIKE', "{$request->search}%")
                    ->orWhere('email', 'LIKE', "{$request->search}%")
                    ->orWhere(DB::raw('CONCAT_WS(" ", reg_firstname, reg_lastname)'), 'LIKE', "{$request->search}%");
            });
        })
        ->when(filter_var($request->with_clients, FILTER_VALIDATE_BOOLEAN), function ($query) use ($request) {
            $query->where(function ($query) {
                $query->whereNotNull('link_social_media_id')
                ->orWhereNotNull('password');
            })
            ->with('contract', function($query) use ($request) {
                $query->with('clients.basicInfo');
            })
            ->addSelect(DB::raw('COUNT(tblm_client_subcon_pers.link_subcon_id) count_clients'))
            ->groupBy('link_subcon_id')
            ->join('tblm_client_sub_contractor', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_client_sub_contractor.reg_link_preregid')
            ->join(config('database')['connections']['mysql2']['database'].'.tblm_client_subcon_pers',
            'tblm_client_sub_contractor.id', '=', 'tblm_client_subcon_pers.link_subcon_id')
            ->when($request->with_clients_count, function ($query) use ($request) {
                $query->having(DB::raw('COUNT(tblm_client_subcon_pers.link_subcon_id)'), 1);
            })
            ->when(!$request->with_clients_count, function ($query) {
                $query->having(DB::raw('COUNT(tblm_client_subcon_pers.link_subcon_id)'), '>=', 2);
            })
            ->orderBy('id', 'desc');
        })
        ->when(!filter_var($request->with_clients, FILTER_VALIDATE_BOOLEAN), function ($query) {
            $query->where(function ($query) {
                $query->whereNotNull('link_social_media_id')
                ->orWhereNotNull('password');
            })
            ->orderBy('reg_firstname', 'asc');
        })
        ->with('registrantType')
        ->whereNotNull('is_verified')
        ->whereNotNull('reg_firstname')
        ->whereIn('registrant_type', [2, 3])
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
    public function getCorporateApps(Request $request)
    {
        $registrants = tap(OnboardProfileBasicInfo::
        selectRaw(
            'tblm_a_onboard_prereg.id,
            tblm_b_onboard_actreg_basic.reg_link_preregid,
            CONCAT_WS(" ", tblm_b_onboard_actreg_basic.reg_firstname, tblm_b_onboard_actreg_basic.reg_lastname) AS complete_name'
        )
        ->join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->where('email', 'LIKE', "{$request->search}%");
                $query->orWhere(DB::raw('CONCAT_WS(" ", reg_firstname, reg_lastname)'), 'LIKE', "{$request->search}%");
            });
        })
        ->with('section')
        ->whereHas('section', function ($query) use ($request) {
            $query->when($request->section_id, function ($query) use ($request) {
                $query->where('link_sec_id', '<>', $request->section_id);
            });
        })
        ->orWhereDoesntHave('section')
        ->whereNotNull('is_verified')
        ->whereNotNull('reg_firstname')
        ->where('registrant_type', 3)
        ->orderBy('reg_firstname', 'asc')
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
    public function getClient(Request $request, $remote_contractor_id = null)
    {
        $existing_clients = [];
        $client_remote_contractor = ClientRemoteContractor::where('reg_link_preregid', $remote_contractor_id)->first();
        if($client_remote_contractor) {
            $existing_clients = $client_remote_contractor->clients()->select('link_client_id')->get()->toArray();
        }

        $client_registrants = OnboardProfileBasicInfo::
        select('email')
        ->join('tblm_a_onboard_prereg', 'tblm_b_onboard_actreg_basic.reg_link_preregid', '=', 'tblm_a_onboard_prereg.id')
        ->where('registrant_type', 4)
        ->get()
        ->toArray();

        $clients = Client::
        select('id', 'client_poc AS complete_name')
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($query) use ($request) {
                $query->where('client_email', 'LIKE', "{$request->search}%");
                $query->orWhere('client_name', 'LIKE', "{$request->search}%");
            });
        })
        ->whereIn('client_email', $client_registrants)
        ->whereNotIn('id', $existing_clients)
        ->orderBy('client_poc', 'asc')
        ->paginate($request->limit ? $request->limit : Client::count());

        if( $clients->count() == 0 ) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'data' => $clients,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function convertJobseekerToRemoteContractor(Request $request)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'jobseeker_id' => 'required|exists:tblm_b_onboard_actreg_basic,reg_link_preregid,registrant_type,1',
            'client_id' => 'required|exists:mysql2.tblm_client,id',
            'convert_date' => 'required|date'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $registrant = OnboardProfileBasicInfo::updateOrCreate([
            'reg_link_preregid' => $request->jobseeker_id,
        ],
        [
            'registrant_type' => 2
        ]);

        $client_remote_contractor = ClientRemoteContractor::updateOrCreate([
            'reg_link_preregid' => $registrant->reg_link_preregid
        ],
        [
            'actreg_contractor_id' => $registrant->reg_id,
            'date_contracted' => Carbon::now(),
            'status' => 'ACTIVE',
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

        ClientRemoteContractorPersonnel::create([
            'link_subcon_id' => $client_remote_contractor->id,
            'link_client_id' => $request->client_id,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'data' => $registrant,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignMoreClientToRemoteContractor(Request $request)
    {
        //Set validation
		$validator = Validator::make($request->all(), [
            'remote_contractor_id' => [
                'required',
                Rule::prohibitedIf(OnboardProfileBasicInfo::
                    where('reg_link_preregid', $request->remote_contractor_id)
                    ->where('registrant_type', '<>', 2)
                    ->where('registrant_type', '<>', 3)
                    ->exists()
                )
            ],
            'client_id' => 'required|exists:mysql2.tblm_client,id',
            'convert_date' => 'required|date'
        ]);

        //If validation fails
		if ($validator->fails()) {
			return response()->json([
                'errors' => $validator->errors()
            ], 422);
		}

        $registrant = OnboardProfileBasicInfo::where('reg_link_preregid', $request->remote_contractor_id)->first();

        $client_remote_contractor = ClientRemoteContractor::updateOrCreate([
            'reg_link_preregid' => $registrant->reg_link_preregid,
        ],
        [
            'actreg_contractor_id' => $registrant->reg_id,
            'date_contracted' => Carbon::now(),
            'status' => 'ACTIVE',
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        ClientRemoteContractorPersonnel::create([
            'link_subcon_id' => $client_remote_contractor->id,
            'link_client_id' => $request->client_id,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'data' => $registrant,
        ], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function expired(Request $request)
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
        ->whereNotNull('is_expired')
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

    //TODO: Delete after
    public function checkRegistrantType($email) {
        $registrant = Registrant::where('email', $email)->first();

        if(!$registrant) {
            return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
        }

        if(!$registrant->basicInfo()->first()) {
            return response()->json([
				'success' => false,
				'message' => 'No registrant type found.',
			], 200);
        }

        return response()->json([
            'success' => true,
            'registrant_type_id' => $registrant->basicInfo()->first()->registrant_type,
            'registrant_type_description' => RegistrantType::where('id', $registrant->basicInfo()->first()->registrant_type)->first()->description
        ], 200);
    }
}
