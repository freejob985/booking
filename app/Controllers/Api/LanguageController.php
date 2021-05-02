<?php

namespace App\Controllers\Api;

use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
	public function index(){
		$enabled = is_multi_language();
		$data = [];
		if($enabled){
			$languages = get_languages(true);
			if(!$languages->isEmpty()){
				$data = [
					'status' => true,
					'data' => $languages->toArray()
				];
			}else{
				$data = [
					'status' => false,
					'data' => []
				];
			}
		}else{
			$data = [
				'status' => false,
				'data' => []
			];
		}
		dd($data);
		$this->sendJson($data);
	}
}
