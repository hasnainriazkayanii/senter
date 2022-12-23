<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SportCategory;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use stdClass;

class SportCategoryController extends Controller
{
    use ApiResponse;
    public function index(Request $request){
        $page = $request->has('page') ? $request->get('page') : 1;
        $limit = $request->has('limit') ? $request->get('limit') : 20;
        $categories = SportCategory::limit($limit)->offset(($page - 1) * $limit);
        $categories = SportCategory::orderBy('created_at', 'desc')->paginate($limit);
        // foreach ($categories as $k => $v) {
        //     $categories[$k]->images =  $v->getMedia('icons');
        //     if(empty($categories[$k]->images) || count($categories[$k]->images)==0){
        //         $categories[$k]->images=NULL;
        //     }
        //     $categories[$k]->clearMediaCollection('media');
        // }
        return $this->successResponse($categories, 200);
    }


    public function save(Request $request){
        $post_id = $request->id;
        $rules = [
            'title'=>'required|unique:sport_categories,title,'.$post_id,
        ];
        $messages=[
            'title.required'=>"Title is Required",
            'title.unique'=>'Sport Title exsists',
        ];
       
        $validator = Validator::make($request->all(), $rules,$messages);
        if ($validator->fails()) {
           return $this->errorResponse(422,$validator->messages(),'Validation Error');
        }
        $data = $request->all();
        $cat   =  SportCategory::updateOrCreate(['id' => $post_id], $data);
        if($request->hasFile('icon') && $request->file('icon')->isValid()){
            if(!empty($request->id)){
                $cat->clearMediaCollection('icons');
            }
            $cat->addMediaFromRequest('icon')->toMediaCollection('icons');
        }
        else{
            $cat->clearMediaCollection('icons');
        }
        
        // $cat->icon = $cat->getMedia('icons');
        // if(empty($cat->icon) || count($cat->icon)==0){
        //     $cat->icon=NULL;
        // }
        // $cat->clearMediaCollection('media');
        $message='Sport Added Successfully';
        if(!empty($request->id)){
             $message='Sport Updated Successfully';
        }
        return $this->successResponse($cat, 200, $message);
    }

    public function edit($id){
        $post = SportCategory::find($id);
        if($post){
            
            // $post->icon = $post->getMedia('icons');
            // if(empty($post->icon) || count($post->icon)==0){
            //     $post->icon=NULL;
            // }
            // $post->clearMediaCollection('media');
            return $this->successResponse($post, 200);
        }
        return $this->errorResponse(404,[],'Sport Not Found');
    }

    public function destroy($id)
    {
        try{
            $post=SportCategory::find($id);
            if($post){
                $post->delete();
                    return $this->successResponse($post, 200);

            }
            return $this->errorResponse(404,[],'Sport Not Found');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }

    public function delete_all(){
        try{
          DB::table('sport_categories')->delete();
         return $this->successResponse([], 200,'All Records Deleted Successfully');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }
}