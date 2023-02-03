<?php

namespace App\Http\Controllers\Api\Bullhorn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BullhornApiController extends Controller
{
	const API_USERNAME = 'remotestaff.api.prod';
	const API_PASSWORD = 'RemoteStaff2019#';
	const CLIENT_ID = '90d2b63f-1bf3-4e34-99fc-4f6ac038636a';
	const CLIENT_SECRET = 'c1U6rhmFsDHoh3o9b2ir5AIZ';
	const ENDPOINT_AUTH_CODE = 'https://auth-apac.bullhornstaffing.com/oauth/authorize?%s';
	const ENDPOINT_ACCESS_TOKEN = 'https://auth-apac.bullhornstaffing.com/oauth/token?%s';
	const ENDPOINT_REST_TOKEN = 'https://rest-apac.bullhornstaffing.com/rest-services/login?version=*&access_token=%s';
	// const API_USERNAME = 'remotestaff.api.npe';
	// const API_PASSWORD = 'RemoteREST2019!';
	// const CLIENT_ID = 'b03adec4-03d6-43f0-9e6d-30f7b6576076';
	// const CLIENT_SECRET = 'P6nqk4dJW1L9zeu1LPMHXtM4';
	// const ENDPOINT_AUTH_CODE = 'https://auth-west9.bullhornstaffing.com/oauth/authorize?%s';
	// const ENDPOINT_ACCESS_TOKEN = 'https://auth-west9.bullhornstaffing.com/oauth/token?%s';
	// const ENDPOINT_REST_TOKEN = 'https://rest-west9.bullhornstaffing.com/rest-services/login?version=*&access_token=%s';

	private $access_token;
	private $auth_code;
	private $comm_response;
	private $comm_response_info;
	private $refresh_token;
	private $rest_token;
	private $rest_endpoint_url;



	public function postEntity($entity_type, $entity_id, $data) {

		$this->setBhCreds();
	
		if ($this->rest_endpoint_url == NULL) {
			$this->oAuth();
		}

		$url = sprintf("%sentity/%s/%s", $this->rest_endpoint_url, $entity_type, $entity_id);

		$status_code = 401;
		while ( $status_code == 401 ) {

			$response = Http::withHeaders([
				'BhRestToken' => $this->rest_token
			])->post($url, $data);
			
			$status_code = $response->status();
			
            if ($response->status() == 401) {
				$this->oAuth();
			}

		}

		return $response->json();
	}


	public function getSearch($entity_type, $params) {
		$this->setBhCreds();
	
		if ($this->rest_endpoint_url == NULL) {
			$this->oAuth();
		}

		$url = sprintf("%ssearch/%s", $this->rest_endpoint_url ,$entity_type);

		$status_code = 401;
		while ( $status_code == 401 ) {

			$response = Http::withHeaders([
				'BhRestToken' => $this->rest_token
			])->get($url, $params);
			
			$status_code = $response->status();
			
            if ($response->status() == 401) {
				$this->oAuth();
			}

		}

		return $response->json();
	} 


	public function postQuery($entity_type, $fields, $where, $layout, 
		$show_read_only, $count, $start, $order_by, 
		$meta, $show_editable) {

		$this->setBhCreds();
	
		if ($this->rest_endpoint_url == NULL) {
			$this->oAuth();
		}
	
		// retrueves a list of entities
		
        $url = sprintf("%squery/%s?fields=%s", $this->rest_endpoint_url, $entity_type, $fields);

		if ( $order_by ) {
			$url .= sprintf("&orderBy=%s", $order_by);
		}

		if ( $layout ) {
            $url .= sprintf("&layout=%s", $layout);
		}

		if ( $show_read_only == "true") {
            $url .= sprintf("&showReadOnly=%s", $show_read_only);
		}

		if ( $count != NULL) {
            $url .= sprintf("&count=%s", $count);
		}

        if ( $start ) {
            $url .= sprintf("&start=%s", $start);
		}

        if ( $meta == "off") {
            $url .= sprintf("&meta=%s", $meta);
		}

        if ( $show_editable == "true") {
            $url .= sprintf("&showEditable=%s", $show_editable);
		}

		$status_code = 401;
		while ( $status_code == 401 ) {

			$response = Http::withHeaders([
				'BhRestToken' => $this->rest_token
			])->post($url, $where);
			
			$status_code = $response->status();
			
            if ($response->status() == 401) {
				$this->oAuth();
			}

		}

		return $response->json();
	}

	public function getEntity($entity_type, $entity_id, $params) {

		$this->setBhCreds();
		
		if ($this->rest_endpoint_url == NULL) {
			$this->oAuth();
		}

		$url = sprintf("%sentity/%s/%s", $this->rest_endpoint_url, $entity_type, $entity_id);
		
		$status = 401;
		$loop_count = 0;
		while ($status == 401) {

			$response = Http::withHeaders([
				'BhRestToken' => $this->rest_token
			])->get($url, $params);
			
			$status = $response->status();
			
            if ($response->status() == 401) {
				$this->oAuth();
			}

		}

		return $response->json();
	}

	private function setBhCreds() {
		
		if (Storage::disk('local')->exists('bullhorncreds/creds.json')) {
			$creds = Storage::disk('local')->get('bullhorncreds/creds.json');
			$creds = json_decode($creds, TRUE);
			$this->access_token = $creds['ACCESS_TOKEN'];
			$this->auth_code = $creds['AUTH_CODE'];
			$this->refresh_token = $creds['REFRESH_TOKEN'];
			$this->rest_endpoint_url = $creds['REST_ENDPOINT_URL'];
			$this->rest_token = $creds['REST_TOKEN'];
		}
	}
    
	private function oAuth() {

		if(TRUE !== $this->oAuthSetRestToken()) {
			return FALSE;
		}
		
		if(TRUE !== $this->oAuthLogin()) {
			return FALSE;
		}

		Storage::disk('local')->put('bullhorncreds/creds.json', json_encode(array(
			'ACCESS_TOKEN' => $this->access_token,
			'AUTH_CODE' => $this->auth_code,
			'REFRESH_TOKEN' => $this->refresh_token,
			'REST_TOKEN' => $this->rest_token,
			'REST_ENDPOINT_URL' => $this->rest_endpoint_url
		)));

		return TRUE;

	}

	private function oAuthSetRestToken() {
		// if(!empty($this->refresh_token)) {

		// 	$comm_args = array(
		// 		'grant_type' => 'refresh_token',
		// 		'refresh_token' => $this->refresh_token,
		// 		'client_id' => self::CLIENT_ID,
		// 		'client_secret' => self::CLIENT_SECRET,
		// 	);

		// 	$ch = curl_init();
		// 	curl_setopt($ch, CURLOPT_URL, sprintf(self::ENDPOINT_ACCESS_TOKEN, http_build_query($comm_args)));
		// 	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		// 	curl_setopt($ch, CURLOPT_POST, TRUE);

		// 	$this->comm_response = json_decode(curl_exec($ch), TRUE);
		// 	$this->comm_response_info = curl_getinfo($ch);

		// 	curl_close($ch);

		// 	if(FALSE === $this->comm_response) {
		// 		return FALSE;
		// 	} 
		// 	echo print_r($this->comm_response);
		// 	$this->access_token = $this->comm_response['access_token'];
		// 	$this->refresh_token = $this->comm_response['refresh_token'];
			
		// 	return TRUE;
		// }

		if(FALSE === $this->oAuthSetAuthCode()) {
			return FALSE;
		}

		# Fetch access token
		$comm_args = array(
			'grant_type' => 'authorization_code',
			'code' => $this->auth_code,
			'client_id' => self::CLIENT_ID,
			'client_secret' => self::CLIENT_SECRET,
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, sprintf(self::ENDPOINT_ACCESS_TOKEN, http_build_query($comm_args)));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		
		$this->comm_response  = json_decode(curl_exec($ch), TRUE);
		$this->comm_response_info = curl_getinfo($ch);
		
		curl_close($ch);

		if(FALSE === $this->comm_response) {
			return FALSE;
		} 

		$this->access_token = $this->comm_response['access_token'];
		$this->refresh_token = $this->comm_response['refresh_token'];
		
		return TRUE;
	}

	private function oAuthSetAuthCode() {

		$comm_args = array(
			'client_id' => self::CLIENT_ID,
			'response_type' => 'code',
			'username' => self::API_USERNAME,
			'password' => self::API_PASSWORD,
			'action' => 'Login',
		);

		$comm_url = sprintf(self::ENDPOINT_AUTH_CODE, http_build_query($comm_args));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $comm_url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$this->comm_response = curl_exec($ch);
		$this->comm_response_info = curl_getinfo($ch);

		if(FALSE === $this->comm_response) { 
			return FALSE;
		}

		if(!empty($this->comm_response_info) && preg_match('@\?code=(.*)&@i', $this->comm_response_info['url'], $auth_code)) {
			$this->auth_code = urldecode($auth_code[1]);
			return TRUE;
		}

		return FALSE;
	}

	private function oAuthLogin() {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, sprintf(self::ENDPOINT_REST_TOKEN, urlencode($this->access_token)));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);

		$is_success = FALSE;
		$loog_counter = 0;
		while ( $is_success == FALSE ) {

			$this->comm_response = json_decode(curl_exec($ch), TRUE);
			$this->comm_response_info = curl_getinfo($ch);
			
			if(FALSE === $this->comm_response) {
				return FALSE;
			}

			if ( $this->comm_response_info['http_code'] == 200 ) {
				$is_success = TRUE;
				$this->rest_endpoint_url = $this->comm_response['restUrl'];
				$this->rest_token = $this->comm_response['BhRestToken'];

				return TRUE;
			}

			if ( $loog_counter > 3 ) {
				return FALSE;
			}

		}

	}
}
