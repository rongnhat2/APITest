<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use App\Models\Relation;
use App\Models\Customer;
use App\Repositories\CustomerRepository;
use App\Repositories\RelationRepository;

class RelationController extends Controller
{
    protected $relation;
    protected $customer;

    public function __construct(Relation $relation, Customer $customer)
    {
        $this->relation     		= new RelationRepository($relation);
        $this->customer     		= new CustomerRepository($customer);
    }

	/*
	 *	Hành động add friend
	 *	
	 *	user_id 			đối tượng được add
	 *	
     *	token 				FROM Cookie
	 *	
	*/
    public function createRelation(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);
    	$user_id 		= $request->user_id;

    	if ($customer_id) {
    		// kiểm tra xem có thằng ngu nào định add với chính bản thân không :<
			if ($customer_id == $user_id) {
				return $this->relation->sendResponse("Bạn đang add với chính bạn :< bạn bị ngu à ?" , 200);
			}else{
	    		// lấy ra danh sách bạn bè
	    		$list_friend 		= $this->relation->getListRelation($customer_id);
	    		// kiểm tra đã có relation chưa
				$has_friend 	= $this->relation->checkHasRelation($list_friend, $user_id);
				if ($has_friend) {
					return $this->relation->sendResponse("cố gắng add clgt ?" , 200);
				}else{
					$createRelation 	= $this->relation->createRelation($customer_id, $user_id);
					if ($createRelation) {
						return $this->relation->sendResponse("Addfriend thành công" , 200);
					}else{
						return $this->relation->sendResponse("Có lỗi sảy ra" , 200);
					}
				}
			}
    	}else{
    		return $this->relation->sendResponse("Token không tồn tại :)" , 401);
    	}
    }
	/*
	 *	Hành động accept friend
	 *	
	 *	user_id 			đối tượng được add
	 *	
     *	token 				FROM Cookie
	 *	
	*/
    public function acceptRelation(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);
    	$user_id 		= $request->user_id;

    	if ($customer_id) {
    		// kiểm tra xem có thằng ngu nào định add với chính bản thân không :<
			if ($customer_id == $user_id) {
				return $this->relation->sendResponse("Bạn đang add với chính bạn :< bạn bị ngu à ?" , 200);
			}else{
	    		// lấy ra danh sách bạn bè
	    		$list_friend 		= $this->relation->getListRelation($user_id);
	    		// kiểm tra đã có relation chưa
				$has_friend 	= $this->relation->checkHasRelation($list_friend, $customer_id);
				if ($has_friend) {
					// chấp nhận lời mời kết bạn
					$accpet_friend 	= $this->relation->acceptRelation($customer_id, $user_id);
					if ($accpet_friend) {
						return $this->relation->sendResponse("Accept thành công" , 200);
					}else{
						return $this->relation->sendResponse("Có lỗi sảy ra" , 200);
					}
				}else{
					return $this->relation->sendResponse("Lời mời không tồn tại" , 200);
				}
			}
    	}else{
    		return $this->relation->sendResponse("Token không tồn tại :)" , 401);
    	}
    }

    public function getFriendList(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
			$data = $this->relation->getListFriend($customer_id);
    		return $this->relation->sendResponseWithData("Danh sách bạn" , 200, $data);
    	}else{
    		return $this->relation->sendResponse("Token không tồn tại :)" , 401);
    	}
    }


	/*
	 *	Hành động lấy ra list addfriend
	 *	
	 *	
     *	token 				FROM Cookie
	 *	
	*/
    public function getFriendRequestList(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
			$data = $this->relation->getAddFriendRequest($customer_id);
    		return $this->relation->sendResponseWithData("Danh sách bạn đã add" , 200, $data);
    	}else{
    		return $this->relation->sendResponse("Token không tồn tại :)" , 401);
    	}
    }

	/*
	 *	Hành động lấy ra list người gửi lời mời kết bạn
	 *	
	 *	
     *	token 				FROM Cookie
	 *	
	*/
    public function getFriendResponseList(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
			$data = $this->relation->getAddFriendResponse($customer_id);
    		return $this->relation->sendResponseWithData("Danh sách người gửi lời mời kết bạn" , 200, $data);
    	}else{
    		return $this->relation->sendResponse("Token không tồn tại :)" , 401);
    	}
    }
}
