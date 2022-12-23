<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse{

    protected function successResponse($data, $code = 200,$message = null)
	{
		return response()->json([
			'status'=> 'Success', 
			'message' => $message, 
			'data' => $data,
		], $code);
	}

	protected function errorResponse($code,$errors=[],$message=null)
	{
		return response()->json([
			'status'=>'Failed',
			'message' => $message,
            'errors' => $errors,
		], $code);
	}

}