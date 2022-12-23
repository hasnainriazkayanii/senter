<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerVerification;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Mail\DynamicEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CustomerController extends Controller
{
    use ApiResponse;
    public function index(Request $request)
    {
        try {
            $page = $request->has('page') ? $request->get('page') : 1;
            $limit = $request->has('limit') ? $request->get('limit') : 20;
            $customers = Customer::limit($limit)->offset(($page - 1) * $limit);
            $customers = Customer::withCount(['posts','followers','followings'])
            ->when($request->has('name'), function ($query)use($request) {
                $query->where('first_name','LIKE', "%{$request->input('name')}%")
                ->orWhere('last_name', 'LIKE', "%{$request->input('name')}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
            // foreach ($customers as $k => $v) {
            //     $customers[$k]->profile_image =  $v->getMedia('profile_images');
            //     if (empty($customers[$k]->profile_image) || count($customers[$k]->profile_image) == 0) {
            //         $customers[$k]->profile_image = NULL;
            //     }
            //     $customers[$k]->clearMediaCollection('media');
            // }
            return $this->successResponse($customers, 200);
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }


    public function save(Request $request)
    {
        try {
            $customer_id = $request->id;
            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:customers,email,' . $customer_id,
                // 'bio' => 'required',
                'gender' => 'required',
                'dob' => 'required|date|date_format:Y-m-d',
            ];
            $messages = [
                'first_name.required' => "First Name is Required",
                'last_name.required' => "Sure Name is Required",
                'email.required' => "Email  is Required",
                // 'bio.required' => 'Bio is required',
                'gender.required' => 'Gender is required',
                'dob.required' => 'Date of Birth is required',
                'email.unique' => 'Email already exsists',
            ];
            if (empty($request->id)) {
                $rules['password'] = 'required|min:6';
            }
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return $this->errorResponse(422, $validator->messages(), 'Validation Error');
            }
            $data = $request->all();
            if(isset($data['password'])){
                $data['password'] = Hash::make($data['password']);
            }
            $customer   =  Customer::updateOrCreate(['id' => $customer_id], $data);
            if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
                if (!empty($request->id)) {
                    $customer->clearMediaCollection('profile_images');
                }
                $customer->addMediaFromRequest('profile_image')->toMediaCollection('profile_images');
            } else {
                $customer->clearMediaCollection('profile_images');
            }
            // $customer->profile_image = $customer->getMedia('profile_images');
            // if (empty($customer->profile_image) || count($customer->profile_image) == 0) {
                //     $customer->profile_image = NULL;
            // }
            // $customer->clearMediaCollection('media');
            if($customer){
                
            if (empty($request->id)) {
                $key = random_int(0, 999999);
                $key = str_pad($key, 6, 0, STR_PAD_LEFT);
                $verification = CustomerVerification::create(array(
                    'customer_id'=>$customer->id,
                    'code'=>$key,
                    'type'=>'verification'
                ));
                $customer->code = $verification->code;
                $this->send_email($customer,$customer->email,'Account Verification Email',$verification->type);
                $message = 'Customer Added Successfully';
            }
            else{
                $message = 'Customer Updated Successfully';
            }
                return $this->successResponse($customer, 200, $message);
            }
            return $this->errorResponse(402, [], 'Customer Not Found');
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $customer = Customer::withCount(['posts','followers','followings'])->with(['posts','followers','followings'])->find($id);
            if ($customer) {
                // $customer->profile_image = $customer->getMedia('profile_images');
                // if (empty($customer->profile_image) || count($customer->profile_image) == 0) {
                //     $customer->profile_image = NULL;
                // }
                // $customer->clearMediaCollection('media');
                return $this->successResponse($customer, 200);
            }
            return $this->errorResponse(404,[], 'Customer Not Found');
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }

    public function destroy($id='')
    {
        try {
                $customer = Customer::find($id);
                if ($customer) {
                    $customer->delete();
                    return $this->successResponse($customer, 200);
                }
                return $this->errorResponse(404,[], 'Customer Not Found');
            
        } catch (\Exception $e) {
            return $this->errorResponse(402, [], $e->getMessage());
        }
    }

    public function  login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];
        $messages = [
            'email.required' => "Email  is Required",
            'password.required' => 'Password is required',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return $this->errorResponse(422, $validator->messages(), 'Validation Error');
        }
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $customer   =  Customer::where('email',$request->email)->first();
        if($customer){
            if(Hash::check($request->password,$customer->password)){
                $message = 'Login  Successfully';
                $customerr = Customer::withCount(['posts','followers','followings'])->with(['posts','followers','followings'])->find($customer->id);
                return $this->successResponse($customerr, 200, $message);
            }
            // $customer->profile_image = $customer->getMedia('profile_images');
            // if (empty($customer->profile_image) || count($customer->profile_image) == 0) {
            //     $customer->profile_image = NULL;
            // }
            // $customer->clearMediaCollection('media');
            return $this->errorResponse(404, [], 'Invalid Credentials');
        }
        return $this->errorResponse(404, [], 'Invalid Credentials');
    }

    public function verify_code(Request $request){
        $rules = [
            'code' => 'required|numeric|digits:6',
        ];
        $messages = [
            'code.required' => "Verification Code  is Required",
            'code.numeric' => "Verification Code  is contains only digits",
            'code.digits' => "Verification Code  should be 6 digits",
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return $this->errorResponse(422, $validator->messages(), 'Validation Error');
        }
        $verification = CustomerVerification::where('code',$request->code)->where('type','verification')->first();
        if($verification){
            $customer = Customer::find($verification->customer_id);
            $customer->is_verified = true;
            $customer->save();
            return $this->successResponse($customer, 200, 'Customer Verified Successfully');
        }
        return $this->errorResponse(404, [], 'Invalid Verification Code');
    }

    public function send_verification_code(Request $request){
        $rules = [
            'email' => 'email|required',
        ];
        $messages = [
            'email.required' => "Email is Required",
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return $this->errorResponse(422, $validator->messages(), 'Validation Error');
        }
        $customer = Customer::where('email',$request->email)->first();
        if($customer){
            if($customer->is_verified){
                return $this->errorResponse(402, [], 'Customer is already verified');
            }
            $key = random_int(0, 999999);
            $key = str_pad($key, 6, 0, STR_PAD_LEFT);
            $verification = CustomerVerification::create(array(
                'customer_id'=>$customer->id,
                'code'=>$key,
                'type'=>'verification'
            ));
            $customer->code = $verification->code;
            $this->send_email($customer,$customer->email,'Account Verification',$verification->type);
            return $this->successResponse($customer, 200, 'Code Sent Successfully');
        }
        return $this->errorResponse(404, [], 'Customer Email Not Found');
    }

    public function forget_password(Request $request){
        $rules = [
            'email' => 'email|required',
        ];
        $messages = [
            'email.required' => "Email is Required",
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return $this->errorResponse(422, $validator->messages(), 'Validation Error');
        }
        $customer = Customer::where('email',$request->email)->first();
        if($customer){
            $key = random_int(0, 999999);
            $key = str_pad($key, 6, 0, STR_PAD_LEFT);
            $verification = CustomerVerification::create(array(
                'customer_id'=>$customer->id,
                'code'=>$key,
                'type'=>'password'
            ));
            $customer->code = $verification->code;
            $this->send_email($customer,$customer->email,'Reset Account Password',$verification->type);
            return $this->successResponse($customer, 200, 'Code Sent Successfully');
        }
        return $this->errorResponse(404, [], 'Customer Email Not Found');
    }
    
    public function update_password(Request $request){
        $rules = [
            'password' => 'required|min:8',
            'confirm_password' => 'required|min:8|same:password',
            'customer_id' => 'required|numeric',
        ];
        $messages = [
            'password.required' => "Password  is Required",
            'customer_id.required' => "Customer ID   is Required",
            'customer_id.numeric' => "Customer ID Should be numeric",
            'confirm_password.required' => "Confirm Password is required",
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return $this->errorResponse(422, $validator->messages(), 'Validation Error');
        }
        $customer = Customer::find($request->customer_id);
        if($customer){
            if(Hash::check($request->password,$customer->password)){
                return $this->errorResponse(404, [], 'Please Use new password can not used old password as new');
            }
            $customer->password = Hash::make($request->password);
            $customer->save();
            return $this->successResponse($customer, 200, 'Password Updated Successfully');
        }
        return $this->errorResponse(404, [], 'Invalid Verification Code');
    }

    public function send_email($data,$to,$subject,$type){

        $content = '{{ first_name }} {{ last_name }} please verify your account using the six digit code <b>{{ code }}</b>';
        if($type=='password'){
            $content = '{{ first_name }} {{ last_name }} please verify your email using the six digit code <b>{{ code }}</b> to reset your account password';
        }
       Mail::to($to)->send(new DynamicEmail($data,$subject,$content));
    }

    public function delete_all(){
        try{
          DB::table('customers')->delete();
         return $this->successResponse([], 200,'All Records Deleted Successfully');
        }
        catch ( \Exception $e) {
            return $this->errorResponse(402,[],$e->getMessage());
       }
    }
}
