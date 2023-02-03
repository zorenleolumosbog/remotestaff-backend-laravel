<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client\ContractorRequestForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use DateTime;
use DateTimeZone;

use App\Http\Controllers\Api\Bullhorn\BullhornApiController;

class CrfController extends Controller
{
    
	public function getIndustries()
	{
		// POST QUERY ========================
		$where = array( 'where' => "id>1100000" );
        $post_query = (new BullhornApiController)->postQuery("BusinessSector", "id,name", $where, 
		NULL, 'true', 100, 0, "name", 'off', 'true');	
		
        return response()->json([
            'success' => true,
            'data' => $post_query
        ], 200);
	}

    public function getJobOrder($jobId)
    {
		// GET ENTITY ========================
		$params = array(
			'fields' => 'id',
			'layout' => 'RecordEdit',
		);
        $get_entity = (new BullhornApiController)->getEntity('JobOrder', $jobId, $params);

        return response()->json([
            'success' => true,
            'data' => $get_entity
        ], 200);

    }


    public function getAllJobOrder($page, $count, $clientId)
    {

        if ( $clientId == 0 ) {
            $where = array(
                'where' => "email='".auth()->user()->email."'",
                'fields' => 'id',
            );
            $client = (new BullhornApiController)->postQuery('ClientContact', "id", $where, 
            NULL, 'false', 100, 0, "id", 'off', 'false');

            if ( $client['count'] != 0) {
                $clientId = $client['data'][0]['id'];
            }
        }

        $params = array(
            'query' => "clientContact.id:".$clientId,
            'fields' => 'id,clientContact,dateAdded',
            'layout' => 'RecordEdit',
            'start' => $page * $count,
            'count' => $count,
            'sort' => '-id',
            'showEditable' => true
        );
        
        $get_search = (new BullhornApiController)->getSearch('JobOrder', $params);

        foreach ( $get_search['data'] as $key => $value ) {
            $timestamp = (int)$value['dateAdded'] / 1000.00;
            $date = DateTime::createFromFormat('U', (int)$timestamp);
            $date->setTimeZone(new DateTimeZone('Asia/Manila'));
            $date = $date->format('m/d/Y');
            $get_search['data'][$key]['timestamp'] = (int)$timestamp;
            $get_search['data'][$key]['convertedDatetime'] = $date;
        }

        return response()->json([
            'success' => true,
            'data' => $get_search
        ], 200);
    }
    

	public function updateJobOrder(Request $request)
	{

        // THE DATA WILL BE SAVED IN MYSQL AND UPDATE JOB ORDER IN BULLHORN

        // $sqlIndustries
        $return_data = array();
        
        $crf_sql = ContractorRequestForm::updateOrCreate([
            'bh_jo_id' => $request->joId
        ],[
            'link_prereg_id' => auth()->user()->id,
            'bh_jo_id' => $request->joId,
            'crf_title' => $request->joTitle,
            'crf_no_staffs' => $request->joOpenings,
            'crf_description' => $request->joDescription,
            'crf_timezone' => $request->joTimezone,
            'crf_ofshore' => $request->joOffshore,
            'crf_hourly_rate' => json_encode($request->joHourlyRate),
            'crf_expertise_level' => json_encode($request->joExpertise),
            'crf_adv_skills' => json_encode($request->joAdvSkills),
            'crf_mid_skills' => json_encode($request->joMidSkills),
            'crf_expected_tof' => $request->joExpectedTof,
            'crf_role_obj' => $request->joRoleObj,
            'crf_industry' => json_encode($request->joIndustry),
            'crf_os' => json_encode($request->joOs),
            'crf_required_tools' => json_encode($request->joTools),
            'crf_au_number' => json_encode($request->joAuNumber),
            'crf_monitors' => $request->joMonitors,
            'crf_comm_tools' => $request->joCommTools,
            'crf_existing_team' => json_encode($request->joTeam),
            'crf_company_age' => $request->joCompanyAge,
            'crf_no_employees' => $request->joEmployees,
            'crf_sourcing' => $request->joSourcing,
            'crf_team_size' => $request->joSize,
            'crf_job_type' => $request->joType,
            'createdby' => auth()->user()->id,
            'datecreated' => Carbon::now(),
            'modifiedby' => auth()->user()->id,
            'datemodified' => Carbon::now()
        ]);

        $return_data['sql_return'] = $crf_sql;

        $industries = array();
        foreach ( $request->joIndustry as $industry ) {
            $industries[] = $industry['id'];
        }

		// POST ENTITY ========================
		$data = array();
        $data['title'] = $request->joTitle;
		$data['businessSectors'] = array('replaceAll' => $industries );
		$data['numOpenings'] = $request->joOpenings;
		$data['employmentType'] = $request->joType;
		$data['description'] = $request->joDescription;
		$data['customText2'] = $request->joTimezone;
		$data['customText4'] = $request->joOffshore;
		$data['customText6'] = $request->joHourlyRate['id'];
		$data['customText7'] = $request->joExpertise['id'];
		$data['customTextBlock3'] = $request->joMidSkills;
		$data['customTextBlock2'] = $request->joAdvSkills;
		$data['customText13'] = $request->joExpectedTof;
		$data['customTextBlock1'] = $request->joRoleObj;
		$data['customText17'] = $request->joOs['id'];
		$data['customText18'] = $request->joTools;
		$data['customText19'] = $request->joAuNumber['id'];
		$data['customText20'] = $request->joMonitors;
		$data['customText21'] = $request->joCommTools;
		$data['customText22'] = $request->joTeam['id'];
		$data['customInt5'] = $request->joCompanyAge;
		$data['customText27'] = $request->joEmployees;
		$data['customText8'] = $request->joSourcing;
		$data['customText9'] = $request->joSize;

		$post_entity = (new BullhornApiController)->postEntity('JobOrder', $request->joId, $data);
		
        $return_data['bh_return'] = $post_entity;

        $bh_success = array_key_exists('changedEntityId', $return_data['bh_return']);

        if ( $bh_success == 1 && $crf_sql) {
            $this->sendMail($request->joId);
        }

        return response()->json([
            'success' => true,
            'data' => $return_data
        ], 200);
	}
    
	public function sendMail($job_id){

        $basic_info = auth()->user()->basicInfo()->first();
        $client_name = '';

        if ( $basic_info ) {
            $client_name = $basic_info['reg_firstname'].' '.$basic_info['reg_lastname'];
        }

        Mail::send('email.crfSubmit', ['client_name' => $client_name, 'job_id' => $job_id, 'client_email' => auth()->user()->email], function($message) {

            if (Config::get('app.env') == 'prod' && Config::get('app.lr_email') != false) {
                $message->to(Config::get('app.devs_email'));
                $message->bcc(Config::get('app.lr_email'));
            } else {
                $message->to(Config::get('app.devs_email'));
            }

            $message->subject('Contractor Request Form');
        });
    }
	
}
