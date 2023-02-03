<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\uploadFile;
use App\Models\Users\Onboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mail;
use Carbon\Carbon;
use Auth;
use URL;


class uploadFileController extends Controller
{
   //Attachments
	public function store(Request $request)
	{
         
		$only_one = false;

		if($request->filetype==1){
			$only_one = true;

			$validator = Validator::make($request->all(), [
					'file'     =>  'required|mimes:doc,docx,pdf,png,jpg,jpeg,gif|max:10000',
					'jobseeker_id' => 'required',
				]

			);
			
		}elseif($request->filetype==2){
			$validator = Validator::make($request->all(), [
				'file'     =>  'required|mimes:png,jpg,jpeg,gif,txt|max:10000',
				'jobseeker_id' => 'required',
				]

			);

		}elseif($request->filetype==3){
			$only_one = true;

			$validator = Validator::make($request->all(), [
					'file'     =>  'required|mimes:mp3,wav,ogg|max:10000',
					'jobseeker_id' => 'required',
				]

			);
			
		}elseif($request->filetype==4){
			$only_one = true;
			$validator = Validator::make($request->all(), [
				'file'     =>  'required|mimes:png,jpg,jpeg,gif|max:10000',
				'jobseeker_id' => 'required',
				]

			);

		}else{

			$validator = Validator::make($request->all(), [
				'file'     =>  'required|mimes:doc,docx,pdf,png,jpg,jpeg,mp3,wav,gif,aiff|max:10000',
				'jobseeker_id' => 'required',
				]

			);

			
		}

		//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

			$name = Carbon::now()->timestamp.'_'.$request->file('file')->getClientOriginalName();

			$extension = $request->file('file')->extension();

			$check_filetype = $this->checkFileType($extension);

			if($request->jobseeker_filetype==1 && $check_filetype!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file. diri ->'.$check_filetype
				], 422);
			}elseif($request->jobseeker_filetype==2 && $check_filetype!=2){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}elseif($request->jobseeker_filetype==3 && $check_filetype!=2 || $request->jobseeker_filetype==3 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}elseif($request->jobseeker_filetype==4 && $check_filetype!=2 || $request->jobseeker_filetype==3 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}elseif($request->jobseeker_filetype==5 && $check_filetype!=5){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}elseif($request->jobseeker_filetype==6 && $check_filetype!=2 || $request->jobseeker_filetype==6 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}elseif($request->jobseeker_filetype==7 && $check_filetype!=2 || $request->jobseeker_filetype==7 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}elseif($request->jobseeker_filetype==7 && $check_filetype!=2 || $request->jobseeker_filetype==7 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}elseif($request->jobseeker_filetype==8 && $check_filetype!=2 || $request->jobseeker_filetype==8 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}elseif($request->jobseeker_filetype==9 && $check_filetype!=2 || $request->jobseeker_filetype==9 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file.'
				], 422);
			}

		$public_path = $request->file('file')->store('public/files/attachment');
		$file_path = 'attachment_'.Carbon::now()->timestamp;

		$path = $request->file('file')->storeAs(
			'upload_file/'.$request->jobseeker_id,
			$name,
			's3'
		);

		
		$uploadFile = uploadFile::where('filetype_201', '=', $request->jobseeker_id)->where('filetype', '=', $request->filetype)->first();
			
		if($uploadFile){
			$save = $uploadFile;
		}else{
			$save = new uploadFile;
		}

		if($request->upload_file==1){
			$filetype = $check_filetype;
		}else{
			$filetype = $request->filetype;
		}

        $save->filename =  $name;
		$save->path = $path;
		$save->filetype_201 = $request->jobseeker_id;
		$save->filetype = $request->filetype;
		$save->dateuploaded = Carbon::now();
		$save->uploadby = $request->jobseeker_id;
		$save->createdby = $request->jobseeker_id;;
		$save->datecreated = Carbon::now();
		$save->modifiedby = $request->jobseeker_id;
		$save->datemodified = Carbon::now();

		$save->save();
		
		$url = Storage::disk('s3')->url($path);

		return response()->json([
			'data' => $save,
			's3_url' => $url,
			'success' => true,
			'message' => 'File Has been uploaded successfully.'
		], 200);

    }

	//Attachments
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

		$uploadFile = uploadFile::where('filetype_201', '=', $request->jobseeker_id);

		if(!empty($request->order)){
			$uploadFile->orderBy('dateuploaded', $request->order);
		}

		$data_json = array();

		$uploadFile = $uploadFile->get();

		$currentURL = URL::to('/');

		foreach($uploadFile as $upload_file){

				array_push($data_json,array(
				'id' => $upload_file->id,
				'filetype_201' => $upload_file->filetype_201,
				'filename' => $upload_file->filename, 
				'path' => $this->presignedUpload($upload_file->path),
				'download'=> $currentURL.'/api/download-file/'.$upload_file->id,
				'filetype' => $upload_file->filetype,
				'dateuploaded' => $upload_file->dateuploaded,
				'uploadby' => $upload_file->uploadby,
				'createdby' => $upload_file->createdby,
				'datecreated' => $upload_file->datecreated,
				'modifiedby' => $upload_file->modifiedby,
				'datemodified' => $upload_file->datemodified)
				);
			}


		return response()->json([
			'data' => $data_json,
			'success' => true,
		], 200);
	}


	public function getByFile(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required',
			'filetype' => 'required',
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}
		
		$uploadFile = uploadFile::where('filetype_201', '=', $request->jobseeker_id)->where('filetype', '=', $request->filetype)->first();

		if ($uploadFile) {
			$url = Storage::disk('s3')->url($uploadFile->path);
			
			return response()->json([
				'success' => true,
				'data' => [
					'filename' => $uploadFile->filename,
					'url' => $url
				],
			], 200);
		}
	}

	public function delete(Request $request)
	{
		$upload_file = uploadFile::where('filetype_201', '=', $request->jobseeker_id)->where('id', '=', $request->id)->first();
		
		if($upload_file===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete file Attachment
			$upload_file->delete();
			return response()->json([
				'success' => true,
				'message' => 'Successfully deleted file attachment.',
			], 200);
		}
	}

	public function presignedUpload($path)
    {
       		 $s3 = Storage::disk('s3');
   			 return $s3->url($path);
    }


	public function getByFileType(Request $request)
    {
		
		$file_type = DB::table('tblm_hris_file_attach')->get();
	
		return response()->json([
			'data' => $file_type,
			'success' => true,
		], 200);
	}

	public function checkFileType($jobseeker_ftype){
		$image_array = array('jpg','png','gif','jpeg');
		$audio_array = array('mp3','wav','ogg','aac','alac','aiffi');
		$doc_array = array('doc','docx','pdf');

		if(in_array($jobseeker_ftype,$image_array)){
			$file_type = 2;
		}elseif(in_array($jobseeker_ftype,$audio_array)){
			$file_type = 5;
		}elseif(in_array($jobseeker_ftype,$doc_array)){
			$file_type = 1;
		}

		return $file_type;
	}

}
