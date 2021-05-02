<?php

namespace App\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\TermRelation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Sentinel;

class UserController extends Controller
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new User();
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendJson([
                'status' => 0,
                'message' => $validator->errors()
            ]);
        }
        $data = parse_request($request, array_keys($rules));

        try {

            $user = Sentinel::authenticate($data, true);

        } catch (NotActivatedException $e) {
            return $this->sendJson([
                'status' => 0,
                'message' => $e->getMessage()
            ]);

        } catch (ThrottlingException $e) {
            return $this->sendJson([
                'status' => 0,
                'message' => $e->getMessage()
            ]);

        }

        if (isset($user) && is_object($user)) {

            $token = create_api_token($user->getUserId());
            update_user_meta($user->getUserId(), 'access_token', $token);

            return $this->sendJson([
                'status' => 1,
                'message' => __('Logged in successfully.'),
                'token_code' => $token
            ]);
        } else {
            return $this->sendJson([
                'status' => 0,
                'message' => __('The email or password is incorrect')
            ]);
        }
    }

    public function store(Request $request)
    {
        $post = new Post();

        $rules = [
            'post_title' => 'required|string',
            'post_slug' => 'required|string',
            'post_content' => 'string',
            'thumbnail_id' => 'integer',
            'author' => 'required|integer|min:0',
            'status' => 'required|in:publish,draft,trash,revision',
            'created_at' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendJson([
                'status' => 0,
                'message' => $validator->errors()
            ]);
        }

        $meta_rules = [
            'categories' => 'array',
            'tags' => 'array',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendJson([
                'status' => 0,
                'message' => $validator->errors()
            ]);
        }

        $data = parse_request($request, array_keys($rules));

        $new_post = $post->createPost($data);
        $post_object = get_post($new_post, 'post');
        if ($new_post && $post_object) {

            $data = parse_request($request, array_keys($meta_rules));

            $termRelation = new TermRelation();

            /* Category update */
            $categories = $data['categories'];
            if (is_array($categories)) {
                $termRelation->deleteRelationByServiceID($new_post, 'post-category');
                if (!empty($categories)) {
                    foreach ($categories as $termID) {
                        $termRelation->createRelation($termID, $new_post, 'post');
                    }
                }
            }

            /* Tag update */
            $tags = $data['tags'];
            if (is_array($tags)) {
                $termRelation->deleteRelationByServiceID($new_post, 'post-tag');
                if (!empty($tags)) {
                    foreach ($tags as $termID) {
                        $termRelation->createRelation($termID, $new_post, 'post');
                    }
                }
            }

            return $this->sendJson([
                'status' => 1,
                'post' => $post_object,
                'message' => __('Created post successfully')
            ]);

        } else {
            return $this->sendJson([
                'status' => 0,
                'message' => __('Can not create post')
            ]);
        }

    }

    public function show($id, Request $request)
    {
        $lang = $request->get('lang', get_current_language());
        $data = $this->model->getById($id);

        if ($data) {
            $data->post_title = get_translate($data->post_title, $lang);
            $data->post_content = get_translate($data->post_content, $lang);
            $data->thumbnail_url = get_attachment_url($data->thumbnail_id);
            $data->author_name = get_username($data->author);

            //Post categories
            $post_categories = get_category($data->post_id);
            $categories = [];
            if (!empty($post_categories)) {
                foreach ($post_categories as $k => $v) {
                    $categories[] = [
                        'id' => $v->term_id,
                        'link' => get_term_link($v->term_name),
                        'name' => get_translate($v->term_title, $lang)
                    ];
                }
            }

            //Post tags
            $post_tags = get_tag($data->post_id);
            $tags = [];
            if (!empty($post_tags)) {
                foreach ($post_tags as $k => $v) {
                    $tags[] = [
                        'id' => $v->term_id,
                        'link' => get_term_link($v->term_name),
                        'name' => get_translate($v->term_title, $lang)
                    ];
                }
            }

            $data->categories = $categories;
            $data->tags = $tags;

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
