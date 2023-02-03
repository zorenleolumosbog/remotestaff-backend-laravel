<?php

namespace App\Http\Controllers\Api\Users;

use App\Models\Users\fileAttachment;
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


class fileAttachmentController extends Controller
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
			],
			[
				'file.max' => 'File size should not be greater than 10MB.',
				'file.mimes' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
			]

			);

		}elseif($request->filetype==2){
			$validator = Validator::make($request->all(), [
					'file'     =>  'required|mimes:png,jpg,jpeg,gif|max:10000',
					'jobseeker_id' => 'required',
				],
				[
					'file.max' => 'File size should not be greater than 10MB.',
					'file.mimes' => 'Invalid file format. Only png, jpeg, jpg, and gif are allowed.'
				]

			);

		}elseif($request->filetype==3){
			$only_one = true;

			$validator = Validator::make($request->all(), [
					'file'     =>  'required|mimes:mp3,wav,ogg|max:10000',
					'jobseeker_id' => 'required',
				],
				[
					'file.max' => 'File size should not be greater than 10MB.',
					'file.mimes' => 'Invalid file format. Only MP3, AAC, ALAC (m4a), WAV, and AIFF are allowed.'
				]

			);

		}elseif($request->filetype==4){
			$only_one = true;
			$validator = Validator::make($request->all(), [
					'file'     =>  'required|mimes:png,jpg,jpeg,gif|max:10000',
					'jobseeker_id' => 'required',
				],
				[
					'file.max' => 'File size should not be greater than 10MB.',
					'file.mimes' => 'Invalid file format. Only png, jpeg, jpg, and gif are allowed.'
				]

			);

		}elseif($request->filetype==4){
			$only_one = true;
			$validator = Validator::make($request->all(), [
					'file'     =>  'required|mimes:png,jpg,jpeg,gif|max:10000',
					'jobseeker_id' => 'required',
				],
				[
					'file.max' => 'File size should not be greater than 10MB.',
					'file.mimes' => 'Invalid file format. Only png, jpeg, jpg, and gif are allowed.'
				]

			);

		}elseif($request->filetype==6){
			$only_one = true;
			$validator = Validator::make($request->all(), [
					'file'     =>  'required|mimes:mp4,avi,mpeg|max:10000',
					'jobseeker_id' => 'required',
				],
				[
					'file.max' => 'File size should not be greater than 10MB.',
					'file.mimes' => 'Invalid file format. Only mp4,avi and mpeg are allowed.'
				]

			);

		}elseif($request->filetype==7){
			$only_one = true;
				$validator = Validator::make($request->all(), [
					'jobseeker_id' => 'required',
					'external_url' => 'required|url',
					],
					['file.max' => 'File size should not be greater than 10MB.']
				);
		}else{

			$validator = Validator::make($request->all(), [
				'file'     =>  'required|mimes:doc,docx,pdf,png,jpg,jpeg,mp3,wav,gif,aiff|max:10000',
				'jobseeker_id' => 'required',
				],
				[
					'file.max' => 'File size should not be greater than 10MB.',
					'file.mimes' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, gif, MP3, AAC, ALAC (m4a), WAV, and AIFF are allowed.'
				]

			);


		}

		//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}
			//extact url extension
			$file_info = pathinfo($request->external_url);


			if($request->filetype != 7){
				$name = Carbon::now()->timestamp.'_'.$request->file('file')->getClientOriginalName();
				$extension = $request->file('file')->extension();
				$check_filetype = $this->checkFileType($extension);
			}


			if($request->jobseeker_filetype==1 && $check_filetype!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==2 && $check_filetype!=2){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==3 && $check_filetype!=2 || $request->jobseeker_filetype==3 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==4 && $check_filetype!=2 || $request->jobseeker_filetype==3 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==5 && $check_filetype!=5){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only MP3, AAC, ALAC (m4a), WAV, and AIFF are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==6 && $check_filetype!=2 || $request->jobseeker_filetype==6 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==7 && $check_filetype!=2 || $request->jobseeker_filetype==7 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==8 && $check_filetype!=2 || $request->jobseeker_filetype==8 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==9 && $check_filetype!=2 || $request->jobseeker_filetype==9 && $extension!=1){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}elseif($request->jobseeker_filetype==10){
				return response()->json([
					'success' => false,
					'message' => 'Invalid file format. Only doc, docx, pdf, png, jpeg, jpg, and gif are allowed.'
				], 422);
			}

			if($request->filetype!=7){
				$file_path = 'attachment_'.Carbon::now()->timestamp.'.'.$extension;

				$path = $request->file('file')->storeAs(
					'file_attachment/'.$request->jobseeker_id,
					$name,
					's3'
				);
			}

			$fileAttachment = fileAttachment::where('link_regid', '=', $request->jobseeker_id)->where('filetype', '=', $request->filetype)->first();

			if($fileAttachment){
				$save = $fileAttachment;
			}else{
				$save = new fileAttachment;
			}

			if($request->file_attachment==1){
				$filetype = $check_filetype;
			}else{
				$filetype = $request->filetype;
			}


			$save->filename = $request->filetype == 7 ? $file_info['basename'] : $name;
			$save->path = $request->filetype == 7 ? $request->external_url : $path;
			$save->link_regid = $request->jobseeker_id;
			$save->filetype = $request->filetype == 7 ?  $request->filetype : $filetype;
			$save->jobseeker_filetype = $request->jobseeker_filetype;
			$save->fileext = $request->filetype == 7 ? $file_info['extension'] : $extension;
			$save->dateuploaded = Carbon::now();
			$save->uploadby = $request->jobseeker_id;

			$save->save();

			if($request->filetype!=7){
				$url = Storage::disk('s3')->url($path);
			}else{
				$url = $request->external_url;
			}

			return response()->json([
				'data' => $save,
				's3_url' => $url,
				'success' => true,
				'message' => 'File has been uploaded successfully.'
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


		//$fileAttachment = fileAttachment::join('tblm_jobseeker_filetype','tblm_jobseeker_filetype.id','=','tblm_c_onboard_actreg_file_attach.jobseeker_filetype')->where('link_regid', '=', $request->jobseeker_id)->select('tblm_c_onboard_actreg_file_attach.*','tblm_jobseeker_filetype.description as file_type');

		// if(!empty($request->order)){
		// 	$fileAttachment->orderBy('dateuploaded', $request->order);
		// }


		$fileAttachment = fileAttachment::join('tblm_jobseeker_filetype','tblm_jobseeker_filetype.id','=','tblm_c_onboard_actreg_file_attach.jobseeker_filetype')->where('link_regid', '=', $request->jobseeker_id)->select('tblm_c_onboard_actreg_file_attach.*','tblm_jobseeker_filetype.description as file_type')
        ->when($request->search, function ($query) use ($request) {
            $query->where('filename', 'LIKE', "%{$request->search}%");
        })
        ->orderBy('id', $request->orderby)
        ->paginate($request->limit ? $request->limit : fileAttachment::count());

		$fileAttachment->transform(function ($item){
			$currentURL = URL::to('/');

			$item->path = $this->presignedUpload($item->path);
			$item->download = $currentURL.'/api/download-file/'.$item->id;

			return $item;
		});

		return response()->json([
			'data' => $fileAttachment,
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

		$fileAttachment = fileAttachment::where('link_regid', '=', $request->jobseeker_id)->where('filetype', '=', $request->filetype)->first();

		if ($fileAttachment) {
			$url = Storage::disk('s3')->url($fileAttachment->path);

			return response()->json([
				'success' => true,
				'data' => [
					'filename' => $fileAttachment->filename,
					'url' => $url
				],
			], 200);
		}

        return response()->json([
            'success' => false,
            'message' => 'No data found.',
        ], 200);
	}


	public function getVideo(Request $request)
    {
		//set validation
		$validator = Validator::make($request->all(), [
			'jobseeker_id' => 'required'
			]

		);

			//if validation fails
		if ($validator->fails()) {
			return response()->json($validator->errors(), 422);
		}

		$fileAttachment = fileAttachment::where('link_regid', '=', $request->jobseeker_id)->where('filetype', '=', $request->filetype)->first();
        if ($fileAttachment) {
			$url = Storage::disk('s3')->url($fileAttachment->path);

			return response()->json([
				'success' => true,
				'data' => [
					'filename' => $fileAttachment->filename,
					'url' => $url
				],
			], 200);
		}else{
			$external_url = fileAttachment::where('link_regid', '=', $request->jobseeker_id)->where('filetype', '=', 7)->first();

            if($external_url) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'filename' => $external_url->filename,
                        'url' => $external_url->path
                    ],
                ], 200);
            }
		}

        return response()->json([
            'success' => false,
            'message' => 'No data found.',
        ], 200);
	}

	public function delete(Request $request)
	{

		$file_attachment = fileAttachment::where('id', '=', $request->id)->first();
	    Storage::disk('s3')->delete($file_attachment->path);

		if($file_attachment===null){
			return response()->json([
				'success' => false,
				'message' => 'No data found.',
			], 200);
		}else{
			//delete file Attachment
			$file_attachment->delete();
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

	public function download(Request $request){

		$fileAttachment = fileAttachment::where('id', '=', $request->id)->first();

		$headers = [
			'Content-Type'        => 'Content-Type: application',
			'Content-Disposition' => 'attachment; filename="'.$fileAttachment->filename.'"',
		];
		return \Response::make(Storage::disk('s3')->get($fileAttachment->path), 200, $headers);
	}

	//Attachments
	public function getByFileType(Request $request)
    {

		$file_type = DB::table('tblm_filetype')->get();

		return response()->json([
			'data' => $file_type,
			'success' => true,
		], 200);
	}

	//Attachments
	public function getByJbFileType(Request $request)
    {

		$file_type = DB::table('tblm_jobseeker_filetype')->get();

		return response()->json([
			'data' => $file_type,
			'success' => true,
		], 200);
	}

	public function completeFileAttacment($id)
	{
		$onboard_employment_history = DB::table('tblm_e_onboard_work_history')->join('tblm_country','tblm_e_onboard_work_history.we_country_id','=','tblm_country.id')->where('tblm_e_onboard_work_history.we_link_reg_id', $id)->select('tblm_e_onboard_work_history.*','tblm_country.short_desc as country_short_desc','tblm_country.long_desc as country_long_desc')->get();

		$onboard_fileAttachment = fileAttachment::where('link_regid', '=', $id)->get();
		$completed = 0;
		$empty = 0;
		$total = 3;
		$count = 0;

		foreach($onboard_fileAttachment as $fileAttachment){

			if($count==1){
				!empty($fileAttachment->path) ? $completed += 1  :  $empty += 1;
				!empty($fileAttachment->jobseeker_filetype) ? $completed += 1 :  $empty += 1;
				!empty($fileAttachment->filetype) ? $completed += 1 :  $empty += 1;
			}


			$count++;
		}


		$percentage_complete = ($completed/$total);

		return $percentage_complete;

	}

	public function checkFileType($jobseeker_ftype){
		$image_array = array('jpg','png','gif','jpeg');
		$audio_array = array('mp3','wav','ogg','aac','alac','aiffi');
		$doc_array = array('doc','docx','pdf');
		$video_array = array('mpeg','avi','mp4');

		if(in_array($jobseeker_ftype,$image_array)){
			$file_type = 2;
		}elseif(in_array($jobseeker_ftype,$audio_array)){
			$file_type = 5;
		}elseif(in_array($jobseeker_ftype,$doc_array)){
			$file_type = 1;
		}elseif(in_array($jobseeker_ftype,$video_array)){
			$file_type = 3;
		}else{
			$file_type = 0;
		}

		return $file_type;
	}
}
