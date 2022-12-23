<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostComment;
use App\Models\SubComment;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubCommentController extends Controller
{
    use ApiResponse;
    public function index(Request $request,$comment_id){
        try{
            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 10;
            $posts = SubComment::with(['comment_by'])->where('comment_id',$comment_id)
            ->orderBy('created_at', 'desc')->paginate($limit);
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
                'comment_id'=>'required',
                'comment'=>'required',
            ];
            $messages=[
                'customer_id.required'=>'Owner ID is required',
                'comment_id.required','Comment ID is required',
                'comment.required'=>'Comment is required',
            ];
        
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
            return $this->errorResponse(422,$validator->messages(),'Validation Error');
            }
            $data = $request->all();
            $post   =  SubComment::updateOrCreate(['id' => $post_id], $data);
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
            $post=SubComment::find($id);
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
            $post = SubComment::find($id);
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
          DB::table('sub_comments')->delete();
         return $this->successResponse([], 200,'All Records Deleted Successfully');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }
}
