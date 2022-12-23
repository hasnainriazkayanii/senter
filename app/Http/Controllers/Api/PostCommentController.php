<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostComment;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostCommentController extends Controller
{
    use ApiResponse;
    public function index(Request $request,$post_id){
        try{
            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 10;
            $posts = PostComment::withCount(['comment_by','sub_comments'])->with(['comment_by','post'=>function($query){
                $query->withCount(['likes','comments','shares']);
            },'post.owner','sub_comments','sub_comments.comment_by'])->where('post_id',$post_id)
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
                'comment'=>'required',
            ];
            $messages=[
                'customer_id.required'=>'Owner ID is required',
                'post_id.required','Post ID is required',
                'comment.required'=>'Comment is required',
            ];
        
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
            return $this->errorResponse(422,$validator->messages(),'Validation Error');
            }
            $data = $request->all();
            $post   =  PostComment::updateOrCreate(['id' => $post_id], $data);
            $message='Post Comment Added Successfully';
            if(!empty($request->id)){
                $message='Post Comment Updated Successfully';
            }
            return $this->successResponse($post, 200, $message);
        } 
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
        }
    }

   

    public function destroy($id)
    {
        try{
            $post=PostComment::find($id);
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

    public function edit($id){
        try{
            $post = PostComment::find($id);
            if($post){
                return $this->successResponse($post, 200);
            }
            return $this->errorResponse(404,[],'Post Comment Not Found');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }

    public function delete_all(){
        try{
          DB::table('post_comments')->delete();
         return $this->successResponse([], 200,'All Records Deleted Successfully');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }
}
