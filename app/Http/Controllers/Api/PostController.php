<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        try {
            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 20;
            $posts = Post::limit($limit)->offset(($page - 1) * $limit);
            $posts = Post::withCount(['comments', 'likes', 'shares'])->with(['owner', 'sportcategory'])
                ->when($request->has('sport_category_id'), function ($query) use ($request) {
                    $query->where('sport_category_id', $request->input('sport_category_id'));
                })
                ->when($request->has('type'), function ($query) use ($request) {
                    $query->where('type', $request->input('type'));
                })
                ->when($request->has('customer_id'), function ($query) use ($request) {

                    $query->with(['shares' => function ($query2) use ($request) {
                        $query2->where('customer_id', $request->input('customer_id'));
                    }, 'shares.post', 'shares.post.owner'])->where('customer_id', $request->input('customer_id'));
                })
                ->when($request->has('follower_ids'), function ($query) use ($request) {
                    $query->with(['shares' => function ($query2) use ($request) {
                        $query2->whereIn('customer_id', $request->follower_ids);
                    }, 'shares.post', 'shares.post.owner'])->whereIn('customer_id', $request->follower_ids);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($limit);
            // foreach ($posts as $k => $v) {

            //     $posts[$k]->images =  $v->getMedia('images');
            //     if(empty($posts[$k]->images) || count($posts[$k]->images)==0){
            //         $posts[$k]->images=NULL;
            //     }                       
            //     $posts[$k]->thumbnail =  $v->getMedia('thumbnails');
            //     if(empty($posts[$k]->thumbnail) || count($posts[$k]->thumbnail)==0){
            //         $posts[$k]->thumbnail=NULL;
            //     }
            //     $posts[$k]->clearMediaCollection('media');
            //     $posts[$k]->owner->profile_image = $v->owner->getMedia('profile_images');
            //     $posts[$k]->owner->clearMediaCollection('media');
            // }
            return $this->successResponse($posts, 200);
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }


    public function save(Request $request)
    {
        try {
            $post_id = $request->id;
            $rules = [
                'type' => 'required',
                'status' => 'required',
                'customer_id' => 'Required',
                'sport_category_id' => 'Required',
            ];
            $messages = [
                'type.required' => "Type is required",
                'status.required' => 'Status is required',
                'customer_id.required' => 'User is required',
                'sport_category_id.required', 'Category is required'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return $this->errorResponse(422, $validator->messages(), 'Validation Error');
            }
            $data = $request->all();
            $post   =  Post::updateOrCreate(['id' => $post_id], $data);
            if ($request->hasFile('media') && $request->file('media')->isValid()) {
                if (!empty($request->id)) {
                    $post->clearMediaCollection('images');
                }
                $post->addMediaFromRequest('media')->toMediaCollection('images');
            } else {
                $post->clearMediaCollection('images');
            }
            if ($request->hasFile('thumbnail') && $request->file('thumbnail')->isValid()) {
                if (!empty($request->id)) {
                    $post->clearMediaCollection('thumbnails');
                }
                $post->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnails');
            } else {
                $post->clearMediaCollection('thumbnails');
            }
            // $post->images = $post->getMedia('images');
            // if(empty($post->images) || count($post->images)==0){
            //     $post->images=NULL;
            // }
            // $post->thumbnail = $post->getMedia('thumbnails');
            // if(empty($post->thumbnail) || count($post->thumbnail)==0){
            //     $post->thumbnail=NULL;
            // }
            // $post->clearMediaCollection('media');
            $message = 'Post Added Successfully';
            if (!empty($request->id)) {
                $message = 'Post Updated Successfully';
            }
            return $this->successResponse($post, 200, $message);
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $post = Post::find($id);
            if ($post) {
                // $post->images = $post->getMedia('images');
                // if(empty($post->images) || count($post->images)==0){
                //     $post->images=NULL;
                // }
                // $post->thumbnail = $post->getMedia('thumbnails');
                // if(empty($post->thumbnail) || count($post->thumbnail)==0){
                //     $post->thumbnail=NULL;
                // }
                // $post->clearMediaCollection('media');
                return $this->successResponse($post, 200);
            }
            return $this->errorResponse(404, [], 'Post Not Found');
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::find($id);
            if ($post) {
                $post->delete();
                return $this->successResponse($post, 200);
            }
            return $this->errorResponse(404, [], 'Post Not Found');
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }

    public function delete_all(Request $request)
    {
        try {
            if ($request->has('type')) {
                DB::table('posts')->where('type', $request->type)->delete();
            } else if ($request->has('is_test')) {
                DB::table('posts')->where('is_test', $request->is_test)->delete();
            } else if ($request->has('customer_id')) {
                DB::table('posts')->where('customer_id', $request->customer_id)->delete();
            } else {
                DB::table('posts')->delete();
            }
            return $this->successResponse([], 200, 'All Records Deleted Successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }
}
