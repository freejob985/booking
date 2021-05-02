<?php

namespace App\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
	public function __construct() {
		$this->model = new Experience();
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
            $data->durations = get_translate($data->durations, $lang);

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

	        //Itinerary services
            if(!empty($data->itinerary)){
                $itinerary = maybe_unserialize($data->itinerary);
                $itinerary_temp = [];
                foreach ($itinerary as $k => $v){
                    $itinerary_temp[] = $v;
                    $itinerary_temp[$k]['sub_title'] = get_translate($v['sub_title'], $lang);
                    $itinerary_temp[$k]['title'] = get_translate($v['title'], $lang);
                    $itinerary_temp[$k]['description'] = get_translate($v['description'], $lang);
                }
                $data->itinerary = $itinerary_temp;
            }

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

            //Languages
            $languages = $data->languages;
            $languages_temp = [];
            if(!empty($languages)){
                $languages = explode(',', $languages);
                foreach ($languages as $k => $v) {
                    $term = get_term_by('id', $v);
                    if($term) {
                        $languages_temp[] = [
                            'id' => $term->term_id,
                            'link' => get_term_link($term->term_name),
                            'name' => get_translate($term->term_title, $lang)
                        ];
                    }
                }
            }

            $data->languages = $languages_temp;

            //Inclusions
            $inclusions = $data->inclusions;
            $inclusions_temp = [];
            if(!empty($inclusions)){
                $inclusions = explode(',', $inclusions);
                foreach ($inclusions as $k => $v) {
                    $term = get_term_by('id', $v);
                    if($term) {
                        $inclusions_temp[] = [
                            'id' => $term->term_id,
                            'link' => get_term_link($term->term_name),
                            'name' => get_translate($term->term_title, $lang)
                        ];
                    }
                }
            }

            $data->inclusions = $inclusions_temp;

            //Exclusions
            $exclusions = $data->exclusions;
            $exclusions_temp = [];
            if(!empty($exclusions)){
                $exclusions = explode(',', $exclusions);
                foreach ($exclusions as $k => $v) {
                    $term = get_term_by('id', $v);
                    if($term) {
                        $exclusions_temp[] = [
                            'id' => $term->term_id,
                            'link' => get_term_link($term->term_name),
                            'name' => get_translate($term->term_title, $lang)
                        ];
                    }
                }
            }

            $data->exclusions = $exclusions_temp;

            //Experience Type
            $experience_type = $data->experience_type;
            if(!empty($experience_type)){
                $term = get_term_by('id', $experience_type);
                if($term){
                    $data->experience_type = [
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
