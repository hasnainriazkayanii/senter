<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostLike;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostLikeController extends Controller
{
    use ApiResponse;
    public function index(Request $request,$post_id){
        try{
            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 10;
            $posts = PostLike::withCount(['liked_by'])->with(['liked_by','post'=>function($query){
                $query->withCount(['likes','comments','shares']);
            },'post.owner'])->where('post_id',$post_id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
            return $this->successResponse($posts, 200);
        } 
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }


    public function save(Request $request){
        try{
            $post_id = $request->id;
            $rules = [
                'customer_id'=>'required',
                'post_id'=>'required',
            ];
            $messages=[
                'customer_id.required'=>'Owner ID is required',
                'post_id.required','Post ID is required'
            ];
        
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
            return $this->errorResponse(422,$validator->messages(),'Validation Error');
            }
            $data = $request->all();
            $post=PostLike::where('post_id',$request->post_id)->where('customer_id',$request->customer_id)->first();
            if($post){
                $post->delete();
                return $this->successResponse($post, 200,'Post Disliked Successfully');
            }
            else{
                $post   =  PostLike::updateOrCreate(['id' => $post_id], $data);
                $message='Post Liked Added Successfully';
                if(!empty($request->id)){
                    $message='Post Like Updated Successfully';
                }
                return $this->successResponse($post, 200, $message);
            }
        } 
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage() );
        }
    }

   

    public function destroy($post_id,$customer_id)
    {
        try{
            $post=PostLike::where('post_id',$post_id)->where('customer_id',$customer_id)->first();
            if($post){
                $post->delete();
                return $this->successResponse($post, 200);
            }
            return $this->errorResponse(404,[],'Post Not Found');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }
    public function delete_all(){
        try{
          DB::table('post_likes')->delete();
         return $this->successResponse([], 200,'All Records Deleted Successfully');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }
}
