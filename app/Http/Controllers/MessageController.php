<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerInfo;
use App\Models\CustomerDetail;
use App\Models\CustomerBlock;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Repositories\CustomerRepository;
use App\Repositories\PostRepository;
use App\Repositories\RelationRepository;
use App\Repositories\NewsfeedRepository;
use App\Repositories\MessageRepository;

class MessageController extends Controller
{
    protected $customer;
    protected $customer_detail;
    protected $customer_block;
    protected $customer_info;
    protected $conversation;
    protected $message;
    protected $messageBlock;

    public function __construct(Customer $customer, CustomerDetail $customer_detail, CustomerInfo $customer_info, CustomerBlock $customer_block, Conversation $conversation, Message $message, MessageBlock $messageBlock)
    {
        $this->customer             = new CustomerRepository($customer);
        $this->customer_detail      = new CustomerRepository($customer_detail);
        $this->customer_info        = new CustomerRepository($customer_info);
        $this->customer_block       = new CustomerRepository($customer_block);
        $this->conversation       	= new MessageRepository($conversation);
        $this->message       		= new MessageRepository($message);
        $this->messageBlock       	= new MessageRepository($messageBlock);
    }



	/*
	 *	- check token hợp lệ
	 *	
	 *	Block customer trong message
	 *	
     *	token 			FROM Cookie
	 *	user_id 		FROM input
	*/
    public function setBlock(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$user_id	= $request->user_id;
    		if ($this->messageBlock->checkHasBlock($customer_id, $user_id)) {
    			return $this->messageBlock->sendResponse("Bạn đã block người này" , 200);
    		}else{
    			$setBlock = $this->messageBlock->setBlock($customer_id, $user_id);
    			if ($setBlock) {
    				return $this->messageBlock->sendResponse("Block thành công :)" , 200);
    			}else{
    				return $this->messageBlock->sendResponse("Block thất bại :)" , 200);
    			}
    		}
    	}else{
    		return $this->messageBlock->sendResponse("Token không tồn tại :)" , 200);
    	}
    }

	/*
	 *	- check token hợp lệ
	 *	
	 *	bỏ Block customer trong message
	 *	
     *	token 			FROM Cookie
	 *	user_id 		FROM input
	*/
    public function setUnBlock(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$user_id	= $request->user_id;
    		if ($this->messageBlock->checkHasBlock($customer_id, $user_id)) {
    			$setUnBlock = $this->messageBlock->setUnBlock($customer_id, $user_id);
    			if ($setUnBlock) {
    				return $this->messageBlock->sendResponse("bỏ Block thành công :)" , 200);
    			}else{
    				return $this->messageBlock->sendResponse("bỏ Block thất bại :)" , 200);
    			}
    		}else{
    			return $this->messageBlock->sendResponse("Bạn chưa block người này" , 200);
    		}
    	}else{
    		return $this->messageBlock->sendResponse("Token không tồn tại :)" , 200);
    	}
    }



	/*
	 *	- check token hợp lệ
	 *	
	 *	lấy ra tin nhắn của hội thoại
	 *	
     *	token 					FROM Cookie
	 *	conversation_id 		FROM input
	*/
    public function getMessage(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$conversation_id  = $request->conversation_id;
    		$data = $this->message->getMessageOfConver($conversation_id);
			return $this->conversation->sendResponseWithData("Danh sách tin nhắn", 200, $data);
    	}else{
    		return $this->conversation->sendResponse("Token không tồn tại :)" , 200);
    	}
    }
	/*
	 *	- check token hợp lệ
	 *	
	 *	lấy ra tin nhắn của hội thoại
	 *	
     *	token 					FROM Cookie
	 *	conversation_id 		FROM input
	 *	user_id 				FROM input
	*/
    public function deleteConversation(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$conversation_id  = $request->conversation_id;
    		$user_id  = $request->user_id;
    		$data = $this->conversation->deleteUserConversation($customer_id, $user_id, $conversation_id);
    		if ($data) {
    			return $this->conversation->sendResponse("Xóa thành công" , 200);
    		}else{
    			return $this->conversation->sendResponse("Xóa thất bại" , 200);
    		}
    	}else{
    		return $this->conversation->sendResponse("Token không tồn tại :)" , 200);
    	}
    }

	/*
	 *	- check token hợp lệ
	 *	
	 *	tạo conversation
	 *	
     *	token 			FROM Cookie
	 *	user_id 		FROM input
	*/


    public function createConversation(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$user_id	= $request->user_id;
    		// kiểm tra đã tồn tại conversation với user_id hay chưa ?
    		if ($this->conversation->checkHasConversaation($customer_id, $user_id)) {
    			return $this->conversation->sendResponse("Đã tồn tại với user này conversation" , 200);
    		}else{
    			if ($this->messageBlock->checkHasBlock($user_id, $customer_id)) {
    				return $this->conversation->sendResponse("Bạn đã bị block bởi customer" , 200);
    			}else if ($this->messageBlock->checkHasBlock($customer_id, $user_id)) {
    				return $this->conversation->sendResponse("Bạn đang block Customer này" , 200);
    			}else{
	    			$create_conversation = $this->conversation->createConversation($customer_id, $user_id);
	    			// nếu user chưa có conversation với customer thì tạo mới cho user với cùng ID
	    			// nếu đã có thì user vẫn xài ID cũ 
	    			if (!$this->conversation->checkHasConversaation($user_id, $customer_id)) {
	    				$create_conver_user = $this->conversation->createUserConversation($user_id, $customer_id, $create_conversation->conversation_code);
	    			}
    				return $this->conversation->sendResponse("Đã tạo mới conversation" , 200);
    			}
    		}
    	}else{
    		return $this->conversation->sendResponse("Token không tồn tại :)" , 200);
    	}
    }

	/*
	 *	- check token hợp lệ
	 *	
	 *	lấy ra danh sách conversation
	 *	
     *	token 			FROM Cookie
	*/
    public function getConversation(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$list_conversation = $this->conversation->getConversation($customer_id);
    		if ($list_conversation) {
    			return $this->conversation->sendResponseWithData("Danh sách conversation" , 200, $list_conversation);
    		}else{
    			return $this->conversation->sendResponse("chưa có conversation nào :)" , 200);
    		}
    	}else{
    		return $this->conversation->sendResponse("Token không tồn tại :)" , 200);
    	}
    }


	/*
	 *	- check token hợp lệ
	 *	
	 *	tạo tin nhắn
	 *	
     *	token 				FROM Cookie
     *	user_id 			FROM input
     *	message 			FROM input
	*/
    public function createMessage(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$user_id		= $request->user_id;
    		$message_user	= $request->message;

    		$token_conversation = $this->conversation->getMessage($customer_id, $user_id);

    		if ($token_conversation) {
    			$send_message = $this->message->createMessage($customer_id, $message_user, $token_conversation->conversation_code);
    			if ($send_message) {
    				return $this->conversation->sendResponse("Gửi tin nhắn thành công" , 200);
    			}else{
    				return $this->conversation->sendResponse("Gửi tin nhắn thất bại" , 200);
    			}
    		}else{
    			return $this->conversation->sendResponse("chưa tồn tại conversation với user này :)" , 200);
    		}
    	}else{
    		return $this->conversation->sendResponse("Token không tồn tại :)" , 200);
    	}
    }

	/*
	 *	- check token hợp lệ
	 *	
	 *	cập nhật trạng thái đã xem
	 *	
	 *	0: chưa xem || 1: đã xem || -1: đã xóa
	 *	
     *	token 				FROM Cookie
     *	user_id 			FROM input
	*/
    public function updateMessage(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$user_id		= $request->user_id;

    		$token_conversation = $this->conversation->getMessage($customer_id, $user_id);
    		if ($token_conversation) {
	    		$message_read_all = $this->message->updateMessage($token_conversation->conversation_code, $user_id);

	    		if ($message_read_all) {
	    			return $this->conversation->sendResponse("đã đọc hết tin nhắn" , 200);
	    		}else{
	    			return $this->conversation->sendResponse("cập nhật thất bại :)" , 200);
	    		}
    		}else{
    			return $this->conversation->sendResponse("chưa tồn tại conversation với user này :)" , 200);
    		}
    	}else{
    		return $this->conversation->sendResponse("Token không tồn tại :)" , 200);
    	}
    }

	/*
	 *	- check token hợp lệ
	 *	
	 *	cập nhật trạng thái đã xem
	 *	
     *	token 				FROM Cookie
     *	user_id 			FROM input
     *	message_id 			FROM input
	*/
    public function deleteMessage(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$user_id		= $request->user_id;

    		$token_conversation = $this->conversation->getMessage($customer_id, $user_id);
    		if ($token_conversation) {
    			$message_id		= $request->message_id;
	    		$message_delete = $this->message->deleteMessage($token_conversation->conversation_code, $user_id, $message_id);

	    		if ($message_delete) {
	    			return $this->conversation->sendResponse("đã Xóa tin nhắn" , 200);
	    		}else{
	    			return $this->conversation->sendResponse("cập nhật thất bại :)" , 200);
	    		}
    		}else{
    			return $this->conversation->sendResponse("chưa tồn tại conversation với user này :)" , 200);
    		}
    	}else{
    		return $this->conversation->sendResponse("Token không tồn tại :)" , 200);
    	}
    }


}
