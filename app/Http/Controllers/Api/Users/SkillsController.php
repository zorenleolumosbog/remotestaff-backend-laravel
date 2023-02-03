<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\Skills;
use App\Models\Users\SkillLevel;
use App\Models\Users\SkillType;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Mail;
use Carbon\Carbon;
use Auth;


class SkillsController extends Controller
{
	public function store(Request $request)
    {
		

		if($request->type=="add"){

			//set validation

		$skills_user = Skills::where('link_skill_id', '=', $request->skill)->where('link_regid', '=', $request->jobseeker_id)->first();

		if($skills_user!=null){
			return response()->json(array('skill'=>'The skill has already been added yaw.'), 422);
		}else{
			$validator = Validator::make($request->all(), [
				'skill'     => 'required',
				'level'  => 'required',
				'jobseeker_id' => 'required',
				]

			);	

			//if validation fails
			if ($validator->fails()) {
				return response()->json($validator->errors(), 422);
			}
		}

		

			$onboard_skills = Skills::Create([
				'link_skill_id'  => $request->skill,
				'link_level_id'  => $request->level,
				'createdby'  => $request->jobseeker_id,
				'datecreated' => Carbon::now(),
				'link_regid'  => $request->jobseeker_id
			]);
			
			return response()->json([
				'success' => true,
				'message' => 'Successfully update.',
				'data' => $onboard_skills,
			], 200);


		}elseif($request->type=="edit"){

			$skills_reponse = Skills::where('id', '=', $request->id)->first();
				
			if($skills_reponse->link_skill_id!=$request->skill){

				$skills_user = Skills::where('link_skill_id', '=', $request->skill)->where('link_regid', '=', $request->jobseeker_id)->first();

					if($skills_user!=null){
						return response()->json(array('skill'=>'The skill has already been added yaw2.'), 422);
					}else{
						$validator = Validator::make($request->all(), [
							'skill'     => 'required',
							'level'  => 'required',
							'jobseeker_id' => 'required',
							]

						);	

						//if validation fails
						if ($validator->fails()) {
							return response()->json($validator->errors(), 422);
						}
					}

				$onboard_skills = Skills::updateOrCreate(['id' => $request->id],[
					'link_skill_id'  => $request->skill,
					'link_level_id'  => $request->level,
					'modifiedby'  => $request->jobseeker_id,
					'datemodified'  => Carbon::now(),
					'link_regid'  => $request->jobseeker_id
				]);

				return response()->json([
						'success' => true,
						'message' => 'Successfully update educational attainment.',
						'data' => $onboard_skills,
					], 200);

			}else{
					//set validation
					$validator = Validator::make($request->all(), [
						'jobseeker_id' => 'required',
					]

				);

					//if validation fails
				if ($validator->fails()) {
					return response()->json($validator->errors(), 422);
				}

				$onboard_skills = Skills::updateOrCreate(['id' => $request->id],[
					'link_skill_id'  => $request->skill,
					'link_level_id'  => $request->level,
					'modifiedby'  => $request->jobseeker_id,
					'datemodified'  => Carbon::now(),
					'link_regid'  => $request->jobseeker_id
				]);

				return response()->json([
						'success' => true,
						'message' => 'Successfully update educational attainment.',
						'data' => $onboard_skills,
					], 200);

			}
			
			

		}
		
		
	}

	public function get(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required',
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}


		//$onboard_skills = skills::get();
		
		$onboard_skills = skills::where('link_regid', '=', $request->jobseeker_id)->first();
		$onboard_skill_levels = SkillLevel::get();

		$data_skills = array();
		$data_level = array();
		$data_level_entry = array();
		$data_skills_entry = array();
		$onboard_array = array();

		foreach($onboard_skill_levels as $onboard_skill_level){
		
			$onboard_skill_type = skills::where('link_level_id', '=', $onboard_skill_level->id)->where('link_regid', '=', $request->jobseeker_id)->get();
			
			$onboard_skill_type->transform(function ($item){
				$skill_type_label = SkillType::where('id', '=', $item->link_skill_id)->first();
				$skill_level_label = SkillLevel::where('id', '=', $item->link_level_id)->first();
				
				$item->skill_level = $skill_level_label->desc;
				$item->skill_type = $skill_type_label->desc;
				
				return $item;
			});

			$onboard_count = skills::where('link_level_id', '=', $onboard_skill_level->id)->where('link_regid', '=', $request->jobseeker_id)->count();

		
			$count = 0;

			//echo $onboard_count."\n";

			foreach($onboard_skill_type as $type){
				
				$skill_type = SkillType::where('id', '=', $type->link_skill_id)->first();
				$skill_level = SkillLevel::where('id', '=', $type->link_level_id)->first();

				$onboarding = skills::where('link_level_id', '=', $type->link_level_id)->where('link_skill_id', '=', $type->link_skill_id)->where('link_regid', '=', $request->jobseeker_id)->get();

				
				array_push($data_skills, array('onboard_skill_id' => $type->id,'skill_type_id' => $type->link_skill_id, 'skill_desc' => $skill_type->desc));
			

				
				array_push($data_level_entry,$type->link_level_id);
			

				$count++;
			}

			$skill_level = SkillLevel::where('id', '=', $onboard_skill_level->id)->first();
			
			$to_array = $onboard_skill_type->toArray();
			array_push($data_level,array('id'=> $onboard_skill_level->id, 'description'=> $skill_level->desc, 'onboard_skills' => $onboard_skill_type));
			
	
		}
		
		if($onboard_skills===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			return response()->json([
				'success' => true,
				'data' => ['levels' => $data_level],
			], 200);
		}
		
	}




	public function delete(Request $request)
	{
		$onboard_skills = Skills::where('link_regid', '=', $request->jobseeker_id)->where('id', '=', $request->id)->first();
		if($onboard_skills===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete Work from Home Resources
			$onboard_skills->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted.',
			], 200);
		}
	}


	public function getSkills(Request $request)
    {
	
		$onboard_skills = SkillType::get();

		if($onboard_skills===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			return response()->json([
				'success' => true,
				'data' => $onboard_skills,
			], 200);
		}
		
	}

	public function getSkillLevel(Request $request)
    {
	
		$onboard_skills = SkillLevel::get();

		if($onboard_skills===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			return response()->json([
				'success' => true,
				'data' => $onboard_skills,
			], 200);
		}
		
	}


	public function completeSkill($id)
	{
		
		$all = 3;
		$count = Skills::where('link_regid', '=', $id)->count();
		$notNull = Skills::whereNotNull('link_level_id')->where('link_regid', '=', $id)->count();

		if($count > 0 ){
			$percent = ($notNull/$all);
			return $percent;
		}
		
		
		
	}


	
	

}
