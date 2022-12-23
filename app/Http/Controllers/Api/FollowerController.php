<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Follower;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FollowerController extends Controller
{
    use ApiResponse;
    public function index(Request $request,$customer_id){
        try{
            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 10;
            $posts = Follower::with(['customer','followed_by'])->where('customer_id',$customer_id)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
            return $this->successResponse($posts, 200);
        } 
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }

    public function following(Request $request,$customer_id){
        try{
            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 10;
            $posts = Follower::with(['customer'])->where('follower_id',$customer_id)
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
                'follower_id'=>'required',
            ];
            $messages=[
                'customer_id.required'=>'Owner ID is required',
                'follower_id.required','Follower ID is required'
            ];
        
            $validator = Validator::make($request->all(), $rules,$messages);
            if ($validator->fails()) {
            return $this->errorResponse(422,$validator->messages(),'Validation Error');
            }
            $data = $request->all();
            $post=Follower::where('customer_id',$request->customer_id)->where('follower_id',$request->follower_id)->first();
            if(!$post){
                $follwer   =  Follower::updateOrCreate(['id' => $post_id], $data);
                $message='Follower Added Successfully';
                if(!empty($request->id)){
                    $message='Follower  Updated Successfully';
                }
                return $this->successResponse($follwer, 200, $message);
            }
            return $this->errorResponse(402, [], 'Customer is already a follower');
        } 
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage() );
        }
    }

   

    public function destroy($customer_id,$follower_id)
    {
        try{
            $post=Follower::where('customer_id',$customer_id)->where('follower_id',$follower_id)->first();
            if($post){
                $post->delete();
                return $this->successResponse($post, 200);
            }
            return $this->errorResponse(404,[],'Customer Not Found');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }

    public function delete_all(){
        try{
          DB::table('followers')->delete();
         return $this->successResponse([], 200,'All Records Deleted Successfully');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }
}
