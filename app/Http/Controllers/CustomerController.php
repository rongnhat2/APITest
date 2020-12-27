<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use App\Models\Customer;
use App\Models\Newsfeed;
use App\Models\Setting;
use App\Models\CustomerInfo;
use App\Models\CustomerDetail;
use App\Models\CustomerBlock;
use App\Repositories\CustomerRepository;
use App\Repositories\NewsfeedRepository;

class CustomerController extends Controller
{
    protected $customer;
    protected $customer_detail;
    protected $customer_block;
    protected $setting;
    protected $newsfeed;
    protected $customer_info;

    public function __construct(Setting $setting, Customer $customer, CustomerDetail $customer_detail, CustomerInfo $customer_info, CustomerBlock $customer_block, Newsfeed $newsfeed)
    {
        $this->customer             = new CustomerRepository($customer);
        $this->customer_detail      = new CustomerRepository($customer_detail);
        $this->customer_info        = new CustomerRepository($customer_info);
        $this->customer_block       = new CustomerRepository($customer_block);
        $this->setting              = new CustomerRepository($setting);
        $this->newsfeed             = new CustomerRepository($newsfeed);
    }

    /*
     *  email       FROM Input
     *  password    FROM Input
    */
    public function login(Request $request){
        // lấy ra request
        $request_data = $request->All();
        // kiểm tra điều kiện validator
        $validator = $this->customer_detail->login_roles($request_data);

        if($validator->fails()) {
            //  fail trả về các lỗi validator
            return $this->customer_detail->sendResponse($validator->getMessageBag()->toArray(), 400);
        }else{
            if ($this->customer_detail->customerLogin($request)) {
                $customer_id = $this->customer_detail->customerLoginID($request->email);
                $token = $this->customer->createTokenClient($customer_id);
                $data_respon = [
                    'messages'          => __('Login Successful'),
                    'token'             => __($token),
                ];
                return $this->customer_detail->sendResponse($data_respon, 200);
            }
            return $this->customer_detail->sendResponse('Username or Password is incorrect', 200);
        }
    }

    /*
     *  email       FROM Input
     *  password    FROM Input
     *  name        FROM Input
    */
    public function register(Request $request){
        // lấy ra request
        $request_data = $request->All();
        // kiểm tra điều kiện validator
        $validator = $this->customer->customer_credential_roles($request_data);

        if($validator->fails()) {
            //  fail trả về các lỗi validator
            return $this->customer->sendResponse($validator->getMessageBag()->toArray(), 400);
        }else{
            // tạo ngẫu nhiên khóa
            $rand_secret            =  $this->customer->generateSecretKey();
            // chèn khóa vào DB
            $customer_create        =  $this->customer->createSecretKey($rand_secret);
            // tạo thông tin user
            $customer_detail_create =  $this->customer_detail->createCustomer($request, $customer_create->id);
            // tạo thông tin newsfeed user
            $customer_newsfeed      =  $this->newsfeed->createCustomerNewfeed($customer_create->id);
            // tạo thông tin cài dặt
            $customer_setting       =  $this->setting->createCustomerSetting($customer_create->id);
            // tạo token
            $customer_token_create  =  $this->customer->createToken($rand_secret, $customer_create->id);
            // true trả về chuỗi 
            return $this->customer->sendResponse('Register successful', 200);
        }
    }

    /*
     *  tạo code đổi mật khẩu
     *  
     *  Token           FROM Cookie
     *  
     *  password    FROM Input
    */
    public function getVerifyCode(Request $request){
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            $old_password   = $request->password;
            $checkOldPassword   = $this->customer_detail->checkPassword($customer_id, $old_password);
            if ($checkOldPassword) {
                $verify_code    = $this->customer_detail->createVerifyCode($customer_id);
                return $this->customer->sendResponseWithData("Mật khẩu cũ chính xác verify code = " , 200, $verify_code);
            }else{
                return $this->customer->sendResponse("Mật khẩu sai" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }

    /*
     *  check code đổi mật khẩu
     *  
     *  Token           FROM Cookie
     *  
     *  verify_code    FROM Input
    */
    public function checkVerifyCode(Request $request){
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            $verify_code        = $request->verify_code;
            $checkVerifyCode    = $this->customer_detail->checkVerifyCode($customer_id, $verify_code);
            if ($checkVerifyCode) {
                return $this->customer->sendResponse("Mã bảo mật chính xác" , 200);
            }else{
                return $this->customer->sendResponse("Mã bảo mật không chính xác" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }

    /*
     *  đổi mật khẩu
     *  
     *  Token           FROM Cookie
     *  
     *  password    FROM Input
    */
    public function updatePassword(Request $request){
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            $password           = $request->password;
            // tạo ngẫu nhiên khóa
            $rand_secret            =  $this->customer->generateSecretKey();
            
            // tạo mới password
            $password_update        = $this->customer_detail->password_update($customer_id, $password);
            // update secret key
            $secret_key_update      = $this->customer->secretKeyUpdate($customer_id, $rand_secret);

            if ($password_update) {
                return $this->customer->sendResponse("Cập nhật thành công" , 200);
            }else{
                return $this->customer->sendResponse("Cập nhật thất bại" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }

    /*
     *  Tạo thông tin của user
     *  
     *  Token           FROM Cookie
     *  
     *  avatar          FROM INPUT
     *  date_of_birth   FROM INPUT
     *  address         FROM INPUT
     *  telephone       FROM INPUT
    */
    public function getInfo(Request $request){   
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            // Kiểm tra đã tồn tại thông tin cá nhân
            if ($this->customer_info->hasInfo($customer_id)) {
                $infor = $this->customer_info->getInfo($customer_id, $request);
                return $this->customer->sendResponseWithData("thông tin customer" , 200, $infor);
            }else{
                return $this->customer->sendResponse("có lỗi sảy ra: thông tin customer chưa tồn tại" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }
    /*
     *  Tạo thông tin của user
     *  
     *  Token           FROM Cookie
     *  
     *  avatar          FROM INPUT
     *  date_of_birth   FROM INPUT
     *  address         FROM INPUT
     *  telephone       FROM INPUT
    */
    public function createInfo(Request $request){   
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            // Kiểm tra đã tồn tại thông tin cá nhân
            if (!$this->customer_info->hasInfo($customer_id)) {
                if ($this->customer_info->createInfo($customer_id, $request)) {
                    return $this->customer->sendResponse("Tạo thông tin customer thành công :)" , 200);
                }else{
                    return $this->customer->sendResponse("có lỗi sảy ra" , 200);
                }
            }else{
                return $this->customer->sendResponse("có lỗi sảy ra: thông tin customer đã tồn tại" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }
    /*
     *  cập nhật thông tin của user
     *  
     *  Token           FROM Cookie
     *  
     *  avatar          FROM INPUT
     *  date_of_birth   FROM INPUT
     *  address         FROM INPUT
     *  telephone       FROM INPUT
    */
    public function updateInfo(Request $request){   
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            // Kiểm tra đã tồn tại thông tin cá nhân
            if ($this->customer_info->hasInfo($customer_id)) {
                if ($this->customer_info->updateInfo($customer_id, $request)) {
                    return $this->customer->sendResponse("cập nhật thông tin customer thành công :)" , 200);
                }else{
                    return $this->customer->sendResponse("có lỗi sảy ra" , 200);
                }
            }else{
                return $this->customer->sendResponse("có lỗi sảy ra: thông tin customer chưa tồn tại" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }

    /*
     *  token FROM Cookie
    */
    public function logout(Request $request){   
        // lấy ra token
        $token  = $request->token;
        // kiểm tra token hợp lệ
        $checkToken = $this->customer->checkToken($token);
        if ($checkToken) {
            return $this->customer->sendResponse('Đăng xuất', 200);
        }else{
            return $this->customer->sendResponse('Lỗi Token không tồn tại', 500);
        }
    }


    /*
     *  Hành động Lấy ra danh sách user bị block
     *  
     *  token               FROM Cookie
     *  
    */
    public function getListBlock(Request $request){   
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            $list_block     = $this->customer_block->getListBlock($customer_id);
            if ($list_block) {
                return $this->customer->sendResponseWithData("Danh sách block" , 200, $list_block);
            }else{
                return $this->customer->sendResponse("Có lỗi sảy ra" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }

    /*
     *  Hành động Block friend
     *  
     *  user_id             đối tượng được add
     *  
     *  token               FROM Cookie
     *  
    */
    public function block(Request $request){   
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            $user_id    = $request->user_id;
            // kiểm tra đã block chưa
            $has_block     = $this->customer_block->checkHasBlock($customer_id, $user_id);
            if ($has_block) {
                return $this->customer->sendResponse("Bạn đã block người này" , 200);
            }else{
                $check_block            = $this->customer->checkBlock($customer_id, $user_id);
                $check_block_customer   = $this->customer->checkBlockCustomer($user_id);
                if ($check_block) {
                    return $this->customer->sendResponse("Bạn không thể block người này" , 200);
                }else if($check_block_customer){
                    return $this->customer->sendResponse("Customer không tồn tại" , 200);
                }else{
                    $is_block   = $this->customer_block->setBlock($customer_id, $user_id);
                    if ($is_block) {
                        return $this->customer->sendResponse("Đã block" , 200);
                    }else{
                        return $this->customer->sendResponse("Có lỗi sảy ra" , 200);
                    }
                }
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }

    /*
     *  Hành động bỏ Block friend
     *  
     *  user_id             đối tượng được add
     *  
     *  token               FROM Cookie
     *  
    */
    public function unblock(Request $request){   
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            $user_id    = $request->user_id;
            // kiểm tra đã block chưa
            $has_block     = $this->customer_block->checkHasBlock($customer_id, $user_id);
            if ($has_block) {
                if ($check_block) {
                    return $this->customer->sendResponse("Bạn không thể block người này" , 200);
                }else{
                    $is_un_block   = $this->customer_block->setUnBlock($customer_id, $user_id);
                    if ($is_un_block) {
                        return $this->customer->sendResponse("Đã bỏ block" , 200);
                    }else{
                        return $this->customer->sendResponse("Có lỗi sảy ra" , 200);
                    }
                }
            }else{
                return $this->customer->sendResponse("Bạn chưa block người này" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }


    /*
     *  Hành động lấy ra cài dặt người dùng
     *  
     *  token               FROM Cookie
     *  
    */
    public function getSetting(Request $request){   
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            $setting = $this->setting->getCustomerSetting($customer_id);
            if ($setting == null) {
                return $this->customer->sendResponse("Không có cài đặt" , 200);
            }else{
                return $this->customer->sendResponseWithData("Danh sách cài đặt" , 200, $setting);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }
    /*
     *  Hành động cập nhật cài dặt người dùng
     *  
     *  token               FROM Cookie
     *  
    */
    public function setSetting(Request $request){   
        $token          = $request->token;
        $customer_id    = $this->customer->checkToken($token);

        if ($customer_id) {
            $setting = $this->setting->updateCustomerSetting($customer_id, $request);
            if ($setting) {
                return $this->customer->sendResponse("cập nhật thành công" , 200);
            }else{
                return $this->customer->sendResponse("cập nhật thất bại, request API thì hãy nhập đủ các trường :(" , 200);
            }
        }else{
            return $this->customer->sendResponse("Token không tồn tại :)" , 200);
        }
    }



    /*
     *  Kiểm tra token tồn tại hoặc hợp lệ
     *  
     *  input   : request
    */
    public function checkToken(Request $request)
    {
        $token         = $request->token;
        $checkToken    = $this->customer->checkToken($token);
        if ($checkToken) {
            return $this->customer->sendResponse('Has Token', 200);
        }else{
            return $this->customer->sendResponse('Not token', 500);
        }
    }

    // Tọa token cho client
    public function createTokenClient(Request $request)
    {
        $id               = $request->id;
        $checkToken       = $this->customer->createTokenClient($id);
        echo $checkToken;
        if ($checkToken) {
            return $this->customer->sendResponse('Has Token', 200);
        }else{
            return $this->customer->sendResponse('Not token', 500);
        }
    }
}
