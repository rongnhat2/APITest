<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Consts;
use Session;
use DB;
use Hash;

class CustomerRepository extends BaseRepository implements RepositoryInterface
{
    protected $model;

    // Constructor to bind model to repo
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // Get all instances of model
    public function getAll()
    {
        return $this->model->all();
    }

    // create a new record in the database
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    // update record in the database
    public function update(array $data, $id = null)
    {
        $record = $this->find($id);
        return $record->update($data);
    }

    // remove record from the database
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    // show the record with the given id
    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    // Get the associated model
    public function getModel()
    {
        return $this->model;
    }

    // Set the associated model
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    // Eager load database relationships
    public function with($relations)
    {
        return $this->model->with($relations);
    }

    // hàm tạo random secret key
    public function generateSecretKey(){
        $rand = mt_rand(1000000, 9999999);
        return $rand;
    }

    // thêm secret key vào db
    public function createSecretKey($secret_key){
        try {
            DB::beginTransaction();

            $item = $this->model->create([
                'secret_key'        =>  $secret_key,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return $item;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    /*
     *  lấy secret key trên DB
    */
    public function getSecretKey($id){
        return $this->model->findOrFail($id)->secret_key;
    }
    /*
     *  lấy token key trên DB
    */
    public function getTokenKey($id){
        return $this->model->findOrFail($id)->token;
    }
    
    /*
     *  Tạo token và lưu trên DB
    */
    public function secretKeyUpdate($customer_id, $secret_key){
        $token = Hash::make($customer_id . '$' . $secret_key);
        $this->model->where('id', $customer_id)->update([
            'secret_key' => $secret_key,
            'token' => $token,
        ]);
        return true;
    }

    /*
     *  Tạo token và lưu trên DB
    */
    public function createToken($secret_key, $id){
        $token = Hash::make($id . '$' . $secret_key);
        $this->model->where('id', $id)->update([
            'token' => $token,
        ]);
        return true;
    }

    /*
     *  Tạo token cho client và trả về
     *  
     *  input   : id của customer
     *  output  : token của customer
    */
    public function createTokenClient($id){
        // key trong DB
        $secret_key   = $this->model->findOrFail($id)->secret_key;
        // Tạo key cho client
        $token = $id . '$' . Hash::make($id . '$' . $secret_key);
        return $token;
    }

    /*
     *  Kiểm tra mật khẩu cũ
     *  
     *  return True or False
    */
    public function checkPassword($customer_id, $old_password){
        $password = $this->model->where('customer_id', $customer_id)->first()->password;
        return Hash::check($old_password, $password);
    }
    /*
     *  Tạo Verify Code
     *  
     *  return True or False
    */
    public function createVerifyCode($customer_id){
        $verify_code = mt_rand(1000000, 9999999);
        try {
            DB::beginTransaction();
            $setting = $this->model->where('customer_id', $customer_id)->update([
                'verify_code'       =>  $verify_code,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return $verify_code;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }
    /*
     *  check Verify Code
     *  
     *  return True or False
    */
    public function checkVerifyCode($customer_id, $verify_code){
        $verifycode = $this->model->where('customer_id', $customer_id)->where('verify_code', $verify_code)->first();
        if ($verifycode == null) {
           return false;
        }else{
            return true;
        }
    }
    /*
     *  đổi mật khẩu
     *  
     *  return True or False
    */
    public function password_update($customer_id, $password){
        try {
            DB::beginTransaction();
            $password = $this->model->where('customer_id', $customer_id)->update([
                'password'          =>  Hash::make($password),
                'verify_code'       =>  '',
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }


    /*
     *  Tạo thông tin customer và lưu trên DB
    */
    public function createCustomer($request, $id){
        try {
            DB::beginTransaction();
            $item = $this->model->create([
                'customer_id'       =>  $id,
                'name'              =>  $request->name,
                'email'             =>  $request->email,
                'password'          =>  Hash::make($request->password),
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }
    /*
     *  Tạo thông tin Tọa độ newsfeed
    */
    public function createCustomerNewfeed($id){
        try {
            DB::beginTransaction();
            $item = $this->model->create([
                'customer_id'       =>  $id,
                'prev_id'           =>  '0',
                'index_id'          =>  '0',
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

    /*
     *  Đăng nhập
    */
    public function customerLogin($request){
        $password           = $this->model->where('email', $request->email)->first()->password;
        $login_successful   = Hash::check($request->password, $password);
        return $login_successful;
    }
    /*
     *  lấy id người dùng đăng nhập
    */
    public function customerLoginID($email){
        return $this->model->where('email', $email)->first()->customer_id;
    }

    /*
     *  Kiểm tra người dùng đã bị block hay chưa
    */
    public function checkHasBlock($customer_id, $user_id){
        $is_block = $this->model->where('customer_id_01', $customer_id)->where('customer_id_02', $user_id)->first();
        if ($is_block == null) {
           return false;
        }else{
            return true;
        }
    }

    /*
     *  kiểm tra đối tượng bị block là bản thân
    */
    public function checkBlock($customer_id, $user_id){
        return $customer_id == $user_id ? true : false;
    }

    /* 
     *  kiểm tra đối tượng bị block hợp lệ 
     * hợp lệ trả về true
    */
    public function checkBlockCustomer($user_id){
        $is_block = $this->model->where('id', $user_id)->first();
        if ($is_block == null) {
            return true;
        }else{
            return false;
        }
    }

    /*
     *  trả về danh sách customer bị block bới user
    */
    public function getListBlock($customer_id){
        return $this->model->where('customer_id_01', $customer_id)->get();
    }

    /*
     *  block người dùng
    */
    public function setBlock($customer_id, $user_id){
        try {
            DB::beginTransaction();

            $this->model->create([
                'customer_id_01'=> $customer_id,
                'customer_id_02'=> $user_id,
                "created_at"    =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"    => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
        return true;
    }
    /*
     *  bỏ block người dùng
    */
    public function setUnBlock($customer_id, $user_id){
        try {
            DB::beginTransaction();

            $this->model->where('customer_id_01', $customer_id)->where('customer_id_02', $user_id)->delete();

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
        return true;
    }

    /*
     *  lấy ra cài đặt customer
    */
    public function getCustomerSetting($id){
        return $this->model->where('customer_id', $id)->first();
    }
    /*
     *  tạo mới cài đặt customer
    */
    public function createCustomerSetting($id){
        try {
            DB::beginTransaction();
            $setting = $this->model->create([
                'customer_id'       =>  $id,
                'like_comment'      =>  '0',
                'from_friends'      =>  '0',
                'requested_friend'  =>  '0',
                'suggested_friend'  =>  '0',
                'birthday'          =>  '0',
                'video'             =>  '0',
                'report'            =>  '0',
                'sound_on'          =>  '0',
                'notification_on'   =>  '0',
                'vibrant_on'        =>  '0',
                'led_on'            =>  '0',
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }
    /*
     *  Cập nhật cài đặt customer
    */
    public function updateCustomerSetting($id, $request){
        try {
            DB::beginTransaction();
            $setting = $this->model->where('customer_id', $id)->update([
                'like_comment'      =>  $request->like_comment,
                'from_friends'      =>  $request->from_friends,
                'requested_friend'  =>  $request->requested_friend,
                'suggested_friend'  =>  $request->suggested_friend,
                'birthday'          =>  $request->birthday,
                'video'             =>  $request->video,
                'report'            =>  $request->report,
                'sound_on'          =>  $request->sound_on,
                'notification_on'   =>  $request->notification_on,
                'vibrant_on'        =>  $request->vibrant_on,
                'led_on'            =>  $request->led_on,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    /*
     *  Kiểm tra customer đã tồn tại thông tin cá nhân
    */
    public function hasInfo($customer_id){
        $has_info = $this->model->where('customer_id', $customer_id)->first();
        if ($has_info == null) {
            return false;
        }else{
            return true;
        }
    }


    /*
     *   lấy ra thông tin cá nhân customer
    */
    public function getInfo($customer_id, $request){
        return $this->model->where('customer_id', $customer_id)->with('customer')->first();
    }

    /*
     *  tạo mới thông tin cá nhân customer
    */
    public function createInfo($customer_id, $request){
        try {
            DB::beginTransaction();
            $customer_info = $this->model->create([
                'customer_id'       =>  $customer_id,
                'avatar'            =>  $request->avatar,
                'date_of_birth'     =>  $request->date_of_birth,
                'address'           =>  $request->address,
                'telephone'         =>  $request->telephone,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    /*
     *  cập nhật thông tin cá nhân customer
    */
    public function updateInfo($customer_id, $request){
        try {
            DB::beginTransaction();
            $customer_info = $this->model->where('customer_id', $customer_id)->update([
                'avatar'            =>  $request->avatar,
                'date_of_birth'     =>  $request->date_of_birth,
                'address'           =>  $request->address,
                'telephone'         =>  $request->telephone,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }

    /*
     *  tìm theo số điện thoại
    */
    public function telephoneSearch($search_value){
        return $this->model->where('telephone', 'like', '%'.$search_value.'%')->take(5)->get();
    }

    /*
     *  tìm theo tên
    */
    public function nameSearch($search_value){
        return $this->model->where('name', 'like', '%'.$search_value.'%')->take(5)->get();
    }
    
    /*
     *  tìm theo ID
    */
    public function IDSearch($search_value){
        return $this->model->where('customer_id', 'like', '%'.$search_value.'%')->take(5)->get();
    }
    




    // validator cho createCustomer phía API
    public function customer_credential_roles(array $data)
    {
        $messages = [
            'name.required'     => __('Tên là trường bắt buộc.'),
            'email.required'    => __('Bạn chưa nhập Email.'),
            'email.email'       => __('Email không đúng định dạng.'),
            'email.unique'      => __('Email đã tồn tại.'),
            'password.required' => __('Mật khẩu là trường bắt buộc.'),
            'password.min'      => __('Mật khẩu tối thiểu 8 kí tự.'),
        ];

        $validator = Validator::make($data, [
            'name' =>'required',
            'email' =>'required|email|unique:customer_detail,email',
            'password' => 'required|min:8',
        ], $messages);

        return $validator;
    }  

    // validator cho createCustomer phía API
    public function login_roles(array $data)
    {
        $messages = [
            'email.required'    => __('Bạn chưa nhập Email.'),
            'email.email'       => __('Email không đúng định dạng.'),
            'password.required' => __('Mật khẩu là trường bắt buộc.'),
        ];

        $validator = Validator::make($data, [
            'email' =>'required|email',
            'password' => 'required',
        ], $messages);

        return $validator;
    }  



}
