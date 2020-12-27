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
use App\Models\PostReport;
use App\Models\Customer;
use App\Models\CustomerDetail;
use App\Models\CustomerBlock;
use App\Models\Relation;
use App\Models\Newsfeed;
use App\Repositories\CustomerRepository;
use App\Repositories\PostRepository;
use App\Repositories\RelationRepository;
use App\Repositories\NewsfeedRepository;

class PostController extends Controller
{
    protected $post;
    protected $postBlock;
    protected $postComment;
    protected $postImage;
    protected $postLike;
    protected $postVideo;
    protected $postReport;
    protected $customer;
    protected $customer_detail;
    protected $customer_block;
    protected $relation;
    protected $newsfeed;

    public function __construct(Relation $relation, Post $post, PostBlock $postBlock, PostComment $postComment, PostImage $postImage, PostLike $postLike, PostVideo $postVideo, PostReport $postReport, Customer $customer, CustomerBlock $customer_block, CustomerDetail $customer_detail, Newsfeed $newsfeed)
    {
        $this->post     			= new PostRepository($post);
        $this->postBlock     		= new PostRepository($postBlock);
        $this->postComment     		= new PostRepository($postComment);
        $this->postImage     		= new PostRepository($postImage);
        $this->postLike     		= new PostRepository($postLike);
        $this->postVideo     		= new PostRepository($postVideo);
        $this->postReport     		= new PostRepository($postReport);
        $this->customer     		= new CustomerRepository($customer);
        $this->customer_detail     	= new CustomerRepository($customer_detail);
        $this->customer_block      	= new CustomerRepository($customer_block);
        $this->relation     		= new RelationRepository($relation);
        $this->newsfeed     		= new NewsfeedRepository($newsfeed);
    }


	/*
	 *	- check token hợp lệ
	 *	
     *	token 			FROM Cookie
	 *	
	*/
    public function getNewsfeed(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	$item_const		= 2;
    	$item_max		= 4; // sai số +- $item_const

    	$item 			= array();

    	if ($customer_id) {
    		// lấy id bản ghi cuối cùng
    		$last_id 	= $this->post->getLastID();
    		// lấy ra các index của user
    		$index_id 		= $this->newsfeed->getIndexID($customer_id);
    		$prev_id 		= $this->newsfeed->getPrevID($customer_id);
    		//  thiết lập mặc định cho index_id
    		if ($index_id == 0) {
    			$index_id 	= $this->newsfeed->setIndexID($customer_id, $last_id);
    		}
    		//  thiết lập mặc định cho prev_id
    		if ($prev_id <= 1) {
    			$prev_id 	= $this->newsfeed->setPrevID($customer_id, $index_id);
    		}
    		// mặc định xong thì lấy ra gán lại, hoặc thông báo đã hết bài đăng
    		// $prev_id 		= $this->newsfeed->getPrevID($customer_id);
    		while (sizeof($item) <= $item_max) {
    			// đã duyệt hết bản ghi
    			if ($index_id == $last_id && $prev_id == 1) {
    				break;
    			}else{
		    		// kiểm tra có phần tử mới hay không ?
		    		$has_new_item = $this->newsfeed->hasNewItem($last_id, $index_id);
		    		if ($has_new_item) {
		    			$next_id = $index_id + $item_const;
		    			$next_id = $next_id > $last_id ? $last_id : $next_id;
		    			//  lấy ra item mới
		    			$post_item 	= $this->post->getNewItem($index_id, $next_id, $item_const);
		    			foreach ($post_item as $key => $value) {
		    				if (!$this->post->postHasRemove($value->id) && !$this->post->postHasBanner($value->id)) {
		    					if ($this->post->postIsUses($customer_id, $value->id)) {
		    						array_push($item, $value);
		    					}else{
		    						if ($this->relation->checkHasFriend($customer_id, $value->customer_id) && $value->state != 0) {
									    // kiểm tra đã block chưa
									    $has_block     = $this->customer_block->checkHasBlock($value->customer_id, $customer_id);
		    							if (!$has_block) {
		    								array_push($item, $value);
		    							}
		    						}
		    					}
		    				}
		    			}
		    			$index_id = $next_id;
	    				$this->newsfeed->setIndexID($customer_id, $next_id);
		    		}else{
		    			$prev_load_id = $prev_id - $item_const;
		    			$prev_load_id = $prev_load_id < 1 ? 1 : $prev_load_id;
		    			//  lấy ra item cũ
		    			$post_item 	= $this->post->getPrevItem($prev_id, $prev_load_id, $item_const);
		    			foreach ($post_item as $key => $value) {
		    				if (!$this->post->postHasRemove($value->id) && !$this->post->postHasBanner($value->id)) {
		    					if ($this->post->postIsUses($customer_id, $value->id)) {
		    						array_push($item, $value);
		    					}else{
		    						if ($this->relation->checkHasFriend($customer_id, $value->customer_id) && $value->state != 0) {
									    // kiểm tra đã block chưa
									    $has_block     = $this->customer_block->checkHasBlock($value->customer_id, $customer_id);
		    							if (!$has_block) {
		    								array_push($item, $value);
		    							}
		    						}
		    					}
		    				}
		    			}
		    			$prev_id = $prev_load_id;
	    				$this->newsfeed->setPrevID($customer_id, $prev_id);
		    		}
    			}
    		}
    		if (sizeof($item) != 0) {
    			return $this->post->sendResponseWithData("List post", 200, $item);
    		}else{
    			return $this->post->sendResponse("bạn đã xem hết tất cả bài viết" , 200);
    		}
    	}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 401);
    	}
    }

	/*
	 *	- check token hợp lệ
	 *	
	 *	
     *	token 			FROM Cookie
     *	post_id 		FROM url 		
	 *	
	*/
    public function get(Request $request){
    	$post_id 		= $request->post_id;
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
				$post_customer_id =  $this->post->getPostCustomerID($post_id);

	    		// Kiểm tra post bị xóa chưa ?
	    		if (!$this->post->postHasRemove($post_id)) {
	    			// Kiểm tra post có bị ban không ?
		    		if ($this->post->postHasBanner($post_id)) {
		    			return $this->post->sendResponse("Post bị tố cáo cmnr :)" , 401);
		    		}else{
	    				// Kiểm tra bạn là chủ post ?
		    			if ($this->post->postIsUses($customer_id, $post_id)) {
			        		return $this->post->sendResponseWithData("Bạn là chủ post", 200, $post_data);
		    			}else{
		    				// check block
		    				if ($this->postBlock->postIsBlock($customer_id, $post_id)) {
		    					return $this->post->sendResponse("bị ban khỏi post :)" , 401);
		    				}else if($this->customer_block->checkHasBlock($value->customer_id, $customer_id)){
		    					return $this->post->sendResponse("bị Block khỏi customer :)" , 401);
		    				}else{
		    					// check state là bạn bè
								$has_friend 	= $this->relation->checkHasFriend($customer_id, $post_customer_id);
								// status cá nhân || bạn bè || public
		    					if ($post_data->state == 0) {
		    						return $this->post->sendResponse("Post cá nhân :)" , 200);
		    					}else if($post_data->state == 1) {
		    						if ($has_friend) {
		    							return $this->post->sendResponseWithData("Post trả về", 200, $post_data);
		    						}else{
		    							return $this->post->sendResponse("Post bạn bè :)" , 200);
		    						}
		    					}else if($post_data->state == 2) {
		    						return $this->post->sendResponseWithData("Post trả về", 200, $post_data);
		    					}else{
	    							return $this->post->sendResponse("State không đúng định dạng" , 401);
		    					}
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
     *	Tạo bài post mới
     *	
     *	token 			FROM Cookie
     *	description 	FROM Input 		// Mô tả bài viết -> nullable
     *	state 			FROM Input 		// Trạng thái bài viết ( -1: remove, 0: private, 1: friend, 2:public )
     *  media_value     FROM Input 		// xác định xem đối tượng media là ảnh hay video ( 0: Không chứa, 1: Ảnh, 2:Video )
     *  image[]    		FROM Input
     *  video[]        	FROM Input
     *  banner[]        FROM Input 		// những user bị ban khỏi bài
    */
    public function create(Request $request){
    	$token 			= $request->token;
    	$media_value 	= $request->media_value;
    	$customer_id 	= $this->customer->checkToken($token);

    	$banner 		= $request->banner;

    	// Kiểm tra có chứa media hay không
		if ($media_value == 0 ) {
			$has_media	= 0;
		}else if($media_value == 1 || $media_value == 2){
			$has_media	= 1;
		}else{
			$has_media	= 2;
		}

    	if ($customer_id) {
    		// Tạo media nếu có
	    	if ($has_media <= 1) {
	    		// tạo bài viết và trả về ID bài viết
		    	$post_id 		= $this->post->createPost($customer_id, $request);
		    	if ($post_id) {
		    		// tạo list user bị ban khỏi post
		    		$banner_user 	= $this->postBlock->createBanner($banner, $post_id);
		    		if ($media_value == 1) {
		    			$imageCreate = $this->postImage->createPostImage($post_id, $request);
		    		}else{
		    			$imageCreate = $this->postVideo->createPostVideo($post_id, $request);
		    		}
		    	}else{
    				return $this->post->sendResponse("Lỗi Tạo bài viết" , 401);
		    	}
	        	return $this->post->sendResponse($post_id , 200);
	    	}else{
	    		return $this->post->sendResponse("Lỗi media_value" , 401);
	    	}
    	}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 401);
    	}
    }

    /*
     *	cập nhật bài post
     *	
     *	token 			FROM Cookie
     *	post_id 		FROM Input 
     *	description 	FROM Input 		// Mô tả bài viết -> nullable
     *	state 			FROM Input 		// Trạng thái bài viết ( -1: remove, 0: private, 1: friend, 2:public )
     *  media_value     FROM Input 		// xác định xem đối tượng media là ảnh hay video ( 0: Không chứa, 1: Ảnh, 2:Video )
     *  image[]    		FROM Input
     *  video[]        	FROM Input
     *  banner[]        FROM Input 		// những user bị ban khỏi bài
    */
	public function update(Request $request){
    	$token 			= $request->token;
    	$media_value 	= $request->media_value;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$post_id 	= $request->post_id;
    		$banner 		= $request->banner;
	    	// Kiểm tra có chứa media hay không
			if ($media_value == 0 ) {
				$has_media	= 0;
			}else if($media_value == 1 || $media_value == 2){
				$has_media	= 1;
			}else{
				$has_media	= 2;
			}

    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
				// kiểm tra có thể chỉnh sửa post, chỉ chủ post mới sửa được
	    		$has_edit 	= $this->post->postIsUses($customer_id, $post_id);
	    		if ($has_edit) {
		    		// sửa bài viết và trả về ID bài viết
			    	$post_id_update = $this->post->updatePost($post_id);
			    	// xóa các url media cũ
			    	$deleteImage	= $this->postImage->deleteImagePost($post_id);
			    	$deleteVideo	= $this->postVideo->deleteVideoPost($post_id);
			    	// Xóa các banner cũ
			    	$deleteBanner	= $this->postBlock->deleteBannerPost($post_id);

		    		// cập nhật list user bị ban khỏi post
		    		$banner_user 	= $this->postBlock->createBanner($banner, $post_id);
			    	// cập nhật các media mới
	    			if ($has_media <= 1) {
			    		if ($media_value == 1) {
			    			$imageCreate = $this->postImage->createPostImage($post_id, $request);
			    		}else{
			    			$imageCreate = $this->postVideo->createPostVideo($post_id, $request);
			    		}
	    			}else{
	    				return $this->post->sendResponse("Lỗi media_value" , 401);
	    			}
	    			if ($post_id_update) {
    					return $this->post->sendResponse("bạn là chủ post!! đã sửa thành công" , 200);
	    			}else{
    					return $this->post->sendResponse("có lỗi sảy ra" , 200);
	    			}
	    		}else{
	    			return $this->post->sendResponse("bạn đéo phải chủ post!!! ? :)" , 401);
	    		}
			}else{
    			return $this->post->sendResponse("Post không tồn tại ? :)" , 401);
    		}
    	}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 401);
    	}
	}

    /*
     *	xóa bài post
     *	
     *	token 			FROM Cookie
     *	post_id 		FROM Input 
    */
	public function delete(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$post_id 	= $request->post_id;

    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
				// kiểm tra có thể xóa post, chỉ chủ post mới sửa được
	    		$has_delete 	= $this->post->postIsUses($customer_id, $post_id);
	    		if ($has_delete) {
		    		// sửa bài viết và trả về ID bài viết
			    	$post_id_delete = $this->post->deletePost($post_id, $request);
			    	// xóa các url media cũ
			    	$deleteImage	= $this->postImage->deleteImagePost($post_id);
			    	$deleteVideo	= $this->postVideo->deleteVideoPost($post_id);
			    	// Xóa các banner cũ
			    	$deleteBanner	= $this->postBlock->deleteBannerPost($post_id);

	    			if ($post_id_delete) {
    					return $this->post->sendResponse("bạn là chủ post!! xóa thành công" , 200);
	    			}else{
    					return $this->post->sendResponse("có lỗi sảy ra" , 200);
	    			}
	    		}else{
	    			return $this->post->sendResponse("bạn đéo phải chủ post!!! ? :)" , 401);
	    		}
			}else{
    			return $this->post->sendResponse("Post không tồn tại ? :)" , 401);
    		}
    	}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 401);
    	}
	}


    /*
     *	Report bài post
     *	
     *	token 			FROM Cookie
     *	post_id 		FROM Input 
     *	report_value 	FROM Input    // 1: cnội dung nhạy cảm | 2: nội dung phản động | 3: nội dung bạo lực, kinh dị
    */
	public function report(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$post_id 		= $request->post_id;
    		$report_value 	= $request->report_value;

    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
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
	    					$is_report = $this->postReport->sendReport($customer_id, $post_id, $report_value);
	    					if ($is_report) {
	    						return $this->post->sendResponse("Report thành công" , 200);
	    					}else{
	    						return $this->post->sendResponse("Có lỗi sảy ra" , 401);
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
     *	lấy ra danh sách like bài post
     *	
     *	token 			FROM Cookie
     *	post_id 		FROM Input 
    */
	public function getListLike(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$post_id 		= $request->post_id;

    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
				$list_like	= $this->postLike->getListLike($post_id);
				return $this->post->sendResponseWithData("Danh sách customer đã like", 200, $list_like);
			}else{
    			return $this->post->sendResponse("Post không tồn tại ? :)" , 401);
    		}
    	}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 401);
    	}
	}

    /*
     *	like bài post
     *	
     *	token 			FROM Cookie
     *	post_id 		FROM Input 
    */
	public function like(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$post_id 		= $request->post_id;

    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
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
	    					// kiểm tra user đã like chưa
	    					$check_like = $this->postLike->checkLike($customer_id, $post_id);
	    					if ($check_like) {
	    						return $this->post->sendResponse("Bạn đã like bài viết này" , 401);
	    					}else{
		    					$is_like 	= $this->postLike->setLike($customer_id, $post_id);
		    					if ($is_like) {
		    						return $this->post->sendResponse("Like thành công" , 200);
		    					}else{
		    						return $this->post->sendResponse("Có lỗi sảy ra" , 401);
		    					}
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
     *	bỏ like bài post
     *	
     *	token 			FROM Cookie
     *	post_id 		FROM Input 
    */
	public function unlike(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$post_id 		= $request->post_id;

    		// lấy ra dữ liệu của post
			$post_data 		= $this->post->getPostData($customer_id, $post_id);
			if ($post_data) {
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
	    					// kiểm tra user đã like chưa
	    					$check_like = $this->postLike->checkLike($customer_id, $post_id);
	    					if ($check_like) {
		    					$is_like = $this->postLike->setUnLike($customer_id, $post_id);
		    					if ($is_like) {
		    						return $this->post->sendResponse("Bỏ Like thành công" , 200);
		    					}else{
		    						return $this->post->sendResponse("Có lỗi sảy ra" , 401);
		    					}
	    					}else{
	    						return $this->post->sendResponse("Bạn chưa like bài viết này" , 401);
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
