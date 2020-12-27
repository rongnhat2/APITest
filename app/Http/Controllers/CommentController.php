<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use App\Models\Post;
use App\Models\PostBlock;
use App\Models\PostComment;
use App\Models\PostImage;
use App\Models\PostLike;
use App\Models\PostVideo;
use App\Models\Customer;
use App\Models\CustomerDetail;
use App\Models\CustomerBlock;
use App\Models\Relation;
use App\Models\Newsfeed;
use App\Repositories\CustomerRepository;
use App\Repositories\PostRepository;
use App\Repositories\RelationRepository;
use App\Repositories\NewsfeedRepository;

class CommentController extends Controller
{
    protected $post;
    protected $postBlock;
    protected $postComment;
    protected $postImage;
    protected $postLike;
    protected $postVideo;
    protected $customer;
    protected $customer_detail;
    protected $customer_block;
    protected $relation;
    protected $newsfeed;

    public function __construct(Relation $relation, Post $post, PostBlock $postBlock, PostComment $postComment, PostImage $postImage, PostLike $postLike, PostVideo $postVideo, Customer $customer, CustomerBlock $customer_block, CustomerDetail $customer_detail, Newsfeed $newsfeed)
    {
        $this->post     			= new PostRepository($post);
        $this->postBlock     		= new PostRepository($postBlock);
        $this->postComment     		= new PostRepository($postComment);
        $this->postImage     		= new PostRepository($postImage);
        $this->postLike     		= new PostRepository($postLike);
        $this->postVideo     		= new PostRepository($postVideo);
        $this->customer     		= new CustomerRepository($customer);
        $this->customer_detail     	= new CustomerRepository($customer_detail);
        $this->customer_block       = new CustomerRepository($customer_block);
        $this->relation     		= new RelationRepository($relation);
        $this->newsfeed     		= new NewsfeedRepository($newsfeed);
    }


    /*
     *	lấy ra comment comment
     *	
     *	token 			FROM Cookie
     *	post_id 		FROM Input 		// id bài viết
    */
	public function get(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	$item 			= array();

    	if ($customer_id) {
    		$post_id 	= $request->post_id;
    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
				// lấy id chủ post
				$post_customer_id =  $this->post->getPostCustomerID($post_id);
	    		// Kiểm tra post bị xóa chưa ?
	    		if (!$this->post->postHasRemove($post_id)) {
	    			// Kiểm tra post có bị ban không ?
		    		if ($this->post->postHasBanner($post_id)) {
		    			return $this->post->sendResponse("Post bị tố cáo cmnr :)" , 401);
		    		}else{
	    				// check block
	    				if ($this->postBlock->postIsBlock($customer_id, $post_id)) {
	    					return $this->post->sendResponse("bị ban khỏi post :)" , 401);
	    				}else{
	    					$comment_post = $this->postComment->getPostComment($post_id);
	    					foreach ($comment_post as $key => $value) {
	    						// kiểm tra xem 2 thằng có block nhau k
	    						if (!$this->customer_block->checkHasBlock($value->customer_id, $customer_id) && !$this->customer_block->checkHasBlock($customer_id, $value->customer_id)) {
		    						array_push($item, $value);
		    					}
	    					}
				    		if (sizeof($item) != 0) {
				    			return $this->post->sendResponseWithData("List comment", 200, $item);
				    		}else{
				    			return $this->post->sendResponse("chưa có comment nào" , 200);
				    		}
	    				}
		    		}
		    	}else{
		    		return $this->post->sendResponse("Post bị xóa rồi :)" , 401);
		    	}
			}else{
    			return $this->post->sendResponse("Post không tồn tại ? :)" , 401);
    		}
		}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 401);
    	}
	}

    /*
     *	tạo comment
     *	
     *	token 			FROM Cookie
     *	post_id 		FROM Input 
     *	comment 	FROM Input 		// Mô tả bài viết -> nullable
    */
	public function create(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$post_id 	= $request->post_id;
    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
				// lấy id chủ post
				$post_customer_id =  $this->post->getPostCustomerID($post_id);
	    		// Kiểm tra post bị xóa chưa ?
	    		if (!$this->post->postHasRemove($post_id)) {
	    			// Kiểm tra post có bị ban không ?
		    		if ($this->post->postHasBanner($post_id)) {
		    			return $this->post->sendResponse("Post bị tố cáo cmnr :)" , 401);
		    		}else{
	    				// check block
	    				if ($this->postBlock->postIsBlock($customer_id, $post_id)) {
	    					return $this->post->sendResponse("bị ban khỏi post :)" , 401);
	    				}else{
	    					// check state là bạn bè
							$has_friend 	= $this->relation->checkHasFriend($customer_id, $post_customer_id);
							// status cá nhân || bạn bè || public
	    					if ($post_data->state == 0) {
	    						return $this->post->sendResponse("Post cá nhân :)" , 200);
	    					}else if($post_data->state == 1) {
	    						if ($has_friend) {
									$create_post_comment = $this->postComment->createPostComment($customer_id, $post_id, $request);
	    						}else{
	    							return $this->post->sendResponse("comment chỉ dành cho bạn bè :)" , 200);
	    						}
	    					}else if($post_data->state == 2) {
								$create_post_comment = $this->postComment->createPostComment($customer_id, $post_id, $request);
	    					}else{
    							return $this->post->sendResponse("State không đúng định dạng" , 401);
	    					}
	    					if ($create_post_comment) {
	    						return $this->post->sendResponse("Comment thành công" , 200);
	    					}else{
	    						return $this->post->sendResponse("Lỗi comment" , 200);
	    					}
	    				}
		    		}
		    	}else{
		    		return $this->post->sendResponse("Post bị xóa rồi :)" , 401);
		    	}
			}else{
    			return $this->post->sendResponse("Post không tồn tại ? :)" , 401);
    		}
		}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 401);
    	}
	}
}
