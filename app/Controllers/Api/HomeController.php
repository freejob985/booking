<?php

namespace App\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Home;
use Illuminate\Http\Request;

class HomeController extends Controller
{
	public function __construct() {
		$this->model = new Home();
	}

    public function show($id, Request $request)
    {
        $lang = $request->get('lang', get_current_language());
        $data = $this->model->getById($id);
        if($data){
            $data->post_title = get_translate($data->post_title, $lang);
            $data->post_content = get_translate($data->post_content, $lang);
            $data->post_description = get_translate($data->post_description, $lang);
	        $data->thumbnail_url = get_attachment_url($data->thumbnail_id);
            $data->author_name = get_username($data->author);
	        $data->location_address = get_translate($data->location_address, $lang);
	        $data->location_state = get_translate($data->location_state, $lang);
	        $data->location_country = get_translate($data->location_country, $lang);
	        $data->location_city = get_translate($data->location_city, $lang);
	        $data->cancellation_detail = get_translate($data->cancellation_detail, $lang);
	        $data->text_external_link = get_translate($data->text_external_link, $lang);

	        //Gallery
            $galleries = $data->gallery;
            $galleries_temp = [];
            if(!empty($galleries)){
                $galleries = explode(',', $galleries);
                foreach ($galleries as $k => $v){
                    $img = get_attachment($v);
                    if($img){
                        $galleries_temp[] = $img;
                    }
                }
            }
            $data->gallery = $galleries_temp;

	        //Extra services
            if(!empty($data->extra_services)){
                $extras = maybe_unserialize($data->extra_services);
                $extras_temp = [];
                foreach ($extras as $k => $v){
                    $extras_temp[] = $v;
                    $extras_temp[$k]['name'] = get_translate($v['name'], $lang);
                }
                $data->extra_services = $extras_temp;
            }

            //Home Amenities
            $home_amenities = $data->amenities;
            $amenities = [];
            if(!empty($home_amenities)){
                $home_amenities = explode(',', $home_amenities);
                foreach ($home_amenities as $k => $v) {
                    $term = get_term_by('id', $v);
                    if($term) {
                        $amenities[] = [
                            'id' => $term->term_id,
                            'link' => get_term_link($term->term_name),
                            'name' => get_translate($term->term_title, $lang)
                        ];
                    }
                }
            }

            $data->amenities = $amenities;

            //Home Type
            $home_type = $data->home_type;
            if(!empty($home_type)){
                $term = get_term_by('id', $home_type);
                if($term){
                    $data->home_type = [
                        'id' => $term->term_id,
                        'link' => get_term_link($term->term_name),
                        'name' => get_translate($term->term_title, $lang)
                    ];
                }
            }

	        return $this->sendJson([
		        'status' => true,
		        'data' => $data
	        ]);
        }
	    return $this->sendJson([
		    'status' => false,
		    'message' => __('Can not get data')
	    ]);
    }
}
