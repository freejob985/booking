<?php

namespace App\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
	public function __construct() {
		$this->model = new Car();
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
            $data->gear_shift = get_translate($data->gear_shift, $lang);
            $data->base_price_html = convert_price($data->base_price);

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

            //Car Type
            $car_type = $data->car_type;
            if(!empty($car_type)){
                $term = get_term_by('id', $car_type);
                if($term){
                    $data->car_type = [
                        'id' => $term->term_id,
                        'link' => get_term_link($term->term_name),
                        'name' => get_translate($term->term_title, $lang)
                    ];
                }
            }

            //Features
            $features = $data->features;
            $features_temp = [];
            if(!empty($features)){
                $features = explode(',', $features);
                foreach ($features as $k => $v) {
                    $term = get_term_by('id', $v);
                    if($term) {
                        $features_temp[] = [
                            'id' => $term->term_id,
                            'link' => get_term_link($term->term_name),
                            'name' => get_translate($term->term_title, $lang)
                        ];
                    }
                }
            }

            $data->features = $features_temp;

            //Equipment
            if(!empty($data->equipments)){
                $equipments = maybe_unserialize($data->equipments);
                $equipments_temp = [];
                foreach ($equipments as $k => $v){
                    $term = get_term_by('id', $k);
                    if($term){
                        $equipments_temp[$k] = $v;
                        $equipments_temp[$k]['term'] = [
                            'id' => $term->term_id,
                            'link' => get_term_link($term->term_name),
                            'name' => get_translate($term->term_title, $lang),
                            'price_html' => convert_price($term->term_price),
                            'price' => $term->term_price
                        ];
                    }
                }
                $data->equipments = $equipments_temp;
            }

            //Discount by days
            if(!empty($data->discount_by_day)){
                $discount_by_day = maybe_unserialize($data->discount_by_day);
                $discount_by_day_temp = [];
                foreach ($discount_by_day as $k => $v){
                    $discount_by_day_temp[] = $v;
                    $discount_by_day_temp[$k]['name'] = get_translate($v['name'], $lang);
                }
                $data->discount_by_day = $discount_by_day_temp;
            }

            //Insurance Plan
            if(!empty($data->insurance_plan)){
                $insurance_plan = maybe_unserialize($data->insurance_plan);
                $insurance_plan_temp = [];
                foreach ($insurance_plan as $k => $v){
                    $insurance_plan_temp[] = $v;
                    $insurance_plan_temp[$k]['name'] = get_translate($v['name'], $lang);
                    $insurance_plan_temp[$k]['description'] = get_translate($v['description'], $lang);
                    $insurance_plan_temp[$k]['price_html'] = convert_price($v['price']);
                }
                $data->insurance_plan = $insurance_plan_temp;
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
