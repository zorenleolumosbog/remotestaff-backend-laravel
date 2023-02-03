<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Client;
use App\Models\Users\Onboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SubcontractorController extends Controller
{
    public function getClient(Request $request)
    {
        $user = Onboard::where('id','=',$request->userid)->first();
        $client_email = Client::where('client_email','=',$user->email)->first();

        if($client_email){
            return response()->json([
                'success' => true,
                'client_id' => $client_email->id,
            ], 200);
        }
    }

    public function getStaff(Request $request)
    {

        $staff_info = array();

        $main_db = Config::get('database.connections');

        $_check_duplicate = array();

            $staff = DB::connection('mysql2')->table("tblm_client_subcon_pers")
            ->join($main_db['mysql']['database'].'.tblm_client_sub_contractor','tblm_client_sub_contractor.id','=','tblm_client_subcon_pers.link_subcon_id')
            ->join($main_db['mysql']['database'].'.tblm_b_onboard_actreg_basic',$main_db['mysql']['database'].'.tblm_client_sub_contractor.actreg_contractor_id','=',$main_db['mysql']['database'].'.tblm_b_onboard_actreg_basic.reg_id')
            ->join($main_db['mysql']['database'].'.tblm_a_onboard_prereg',$main_db['mysql']['database'].'.tblm_b_onboard_actreg_basic.reg_link_preregid','=',$main_db['mysql']['database'].'.tblm_a_onboard_prereg.id')
            ->where([
					['tblm_client_subcon_pers.link_client_id', '=', $request->client],
					[$main_db['mysql']['database'].'.tblm_a_onboard_prereg.password', '!=', '']
					])  
                ->groupBy('tblm_b_onboard_actreg_basic.reg_link_preregid')    
            ->get();

            if($staff){
                foreach($staff as $sub){
                    if($staff){
                        $staff_info[] = array('id' => $sub->reg_link_preregid,'complete_name' => ucwords(strtolower($sub->reg_firstname)).' '.ucwords(strtolower($sub->reg_lastname)));
                    }
                }
            }
        
        
        return response()->json([
            'success' => true,
            'data' => $staff_info,
        ], 200);
    

    }  
}
