<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaterPost;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LaterPostsController extends Controller
{
    use ApiResponse;
    public function index(Request $request,$customer_id){
        try{
            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 10;
            $posts = LaterPost::withCount(['post'])->with(['post'=>function($query){
                $query->withCount(['likes','comments','shares']);
            },'post.owner'])->where('customer_id',$customer_id)
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
                'post_id.required','Post ID is required',
            ];
        
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
            return $this->errorResponse(422,$validator->messages(),'Validation Error');
            }
            $data = $request->all();
            $post   =  LaterPost::updateOrCreate(['id' => $post_id], $data);
            $message='Post Share Added Successfully';
            if(!empty($request->id)){
                $message='Post Share Updated Successfully';
            }
            return $this->successResponse($post, 200, $message);
        } 
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage() );
        }
    }

   

    public function destroy($id)
    {
        try{
            $post=LaterPost::find($id);
            if($post){
                $post->delete();
                return $this->successResponse($post, 200,'Deleted Successfully');
            }
            return $this->errorResponse(404,[],'Post Not Found');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }

    public function edit($id){
        try{
            $post = LaterPost::find($id);
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
          DB::table('later_posts')->delete();
         return $this->successResponse([], 200,'All Records Deleted Successfully');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }
}
