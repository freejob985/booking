<?php
/**
 * Created by PhpStorm.
 * Date: 1/7/2020
 * Time: 4:00 PM
 */

namespace App\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Seo;
use Illuminate\Http\Request;
use Sentinel;

class SeoController extends Controller
{

    public function _saveSeo(Request $request)
    {
        $post_id = request()->get('postID');
        $post_encrypt = request()->get('postEncrypt');
        $post_type = request()->get('postType');

        if (!hh_compare_encrypt($post_id, $post_encrypt)) {
            return $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This post is not available')
            ]);
        }
        $post = get_post($post_id, $post_type);
        if (is_null($post)) {
            return $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('This post is not available')
            ]);
        }

        $current_language_switcher = request()->get('current_language_switcher', get_current_language());

        $data = [];
        $seo_keys = get_seo_keys();
        $is_multi_language = is_multi_language();

        foreach ($seo_keys as $seo_key => $translation) {
            if ($is_multi_language) {
                $data[$seo_key] = $translation ? set_translate($seo_key) : request()->get($seo_key) ;
            } else {
                $data[$seo_key] = request()->get($seo_key);
            }
        }
        if (!empty($data)) {
            $data['post_id'] = $post_id;
            $data['post_type'] = $post_type;

            $seo = new Seo();
            $seo_item = $seo->getByPostId($post_id, $post_type);
            if (is_null($seo_item)) {
                $seo_id = $seo->createSeo($data);
            } else {
                $seo_id = $seo->updateSeo($data, $seo_item->seo_id);
            }
            if ($seo_id) {
                $seo_item = $seo->getById($seo_id);
                $seo_render = seo_render($seo_item, $current_language_switcher, $post);
                return $this->sendJson([
                    'status' => 1,
                    'title' => __('System Alert'),
                    'render' => $seo_render
                ]);
            } else {
                return $this->sendJson([
                    'status' => 0,
                    'title' => __('System Alert'),
                    'message' => __('Can not save SEO data. [Error: DB]')
                ]);
            }
        } else {
            return $this->sendJson([
                'status' => 0,
                'title' => __('System Alert'),
                'message' => __('Can not save SEO data')
            ]);
        }
    }

    public function applyData($field, $seo)
    {
        if(is_null($seo)){
            return $field;
        }
        $seo_key = $field['id'];
        if (isset($seo->$seo_key)) {
            $field['value'] = $seo->$seo_key;
        }
        return $field;
    }

    public static function get_inst()
    {
        static $instance;
        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
