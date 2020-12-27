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
use App\Models\CustomerInfo;
use App\Models\Relation;
use App\Models\Newsfeed;
use App\Models\Search;
use App\Repositories\CustomerRepository;
use App\Repositories\PostRepository;
use App\Repositories\RelationRepository;
use App\Repositories\NewsfeedRepository;
use App\Repositories\SearchRepository;

class SearchController extends Controller
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
    protected $customer_info;
    protected $relation;
    protected $newsfeed;
    protected $search;

    public function __construct(Relation $relation, Post $post, PostBlock $postBlock, PostComment $postComment, PostImage $postImage, PostLike $postLike, PostVideo $postVideo, PostReport $postReport, Customer $customer, CustomerInfo $customer_info, CustomerBlock $customer_block, CustomerDetail $customer_detail, Newsfeed $newsfeed, Search $search)
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
        $this->customer_info        = new CustomerRepository($customer_info);
        $this->relation     		= new RelationRepository($relation);
        $this->newsfeed     		= new NewsfeedRepository($newsfeed);
        $this->search     			= new SearchRepository($search);
    }

	/*
	 *	- check token hợp lệ
	 *	
     *	token 			FROM Cookie
	 *	value 			FROM input
	 *	
	 *	trả về 5 user, 5 post
	*/
    public function create(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	$item 			= array();
    	if ($customer_id) {
    		$search_value = $request->value;
    		$telephone_search 	=	$this->customer_info->telephoneSearch($search_value);
    		$username_search 	=	$this->customer_detail->nameSearch($search_value);
    		$id_search 			=	$this->customer_detail->IDSearch($search_value);
    		$post_search 		=	$this->post->postSearch($search_value);

			foreach ($telephone_search as $key => $value) {
			    // kiểm tra đã block chưa
			    $has_block     = $this->customer_block->checkHasBlock($value->customer_id, $customer_id);
				if (!$has_block) {
					array_push($item, $value);
				}
			}
			foreach ($username_search as $key => $value) {
			    // kiểm tra đã block chưa
			    $has_block     = $this->customer_block->checkHasBlock($value->customer_id, $customer_id);
				if (!$has_block) {
					array_push($item, $value);
				}
			}
			foreach ($id_search as $key => $value) {
			    // kiểm tra đã block chưa
			    $has_block     = $this->customer_block->checkHasBlock($value->customer_id, $customer_id);
				if (!$has_block) {
					array_push($item, $value);
				}
			}
			foreach ($post_search as $key => $value) {
			    // kiểm tra đã block chưa
			    $has_block     = $this->customer_block->checkHasBlock($value->customer_id, $customer_id);
				if (!$has_block) {
					array_push($item, $value);
				}
			}
			$this->search->createSearch($customer_id, $search_value);

			return $this->post->sendResponseWithData("kết quả tìm kiếm", 200, $item);
    	}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 200);
    	}
    }

	/*
	 *	- check token hợp lệ
	 *	
     *	token 			FROM Cookie
	 *	
	 *	trả về max = 5 tìm kiếm gần nhất
	*/
    public function get(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);
    	$last_id 		= $this->search->getLastID();
    	$item 			= array();
    	if ($customer_id) {
    		while ($last_id >= 1 && sizeof($item) < 5) {
    			$data = $this->search->getSearch($last_id);
    			if ($data->customer_id == $customer_id) {
					array_push($item, $data);
    			}
    			$last_id--;
    		}
			return $this->post->sendResponseWithData("lịch sử tìm kiếm", 200, $item);
    	}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 200);
    	}
    }

	/*
	 *	- check token hợp lệ
	 *	
     *	token 			FROM Cookie
     *	search_id 		FROM input
	 *	
	 *	trả về max = 5 tìm kiếm gần nhất
	*/
    public function delete(Request $request){
    	$token 			= $request->token;
    	$customer_id 	= $this->customer->checkToken($token);

    	if ($customer_id) {
    		$search_id 			= $request->search_id;
    		
			$data = $this->search->deleteSearch($customer_id, $search_id);
			if ($data) {
    			return $this->post->sendResponse("xóa thành công" , 200);
			}else{
				return $this->post->sendResponse("bạn không thể xóa post này" , 200);
			}
    	}else{
    		return $this->post->sendResponse("Token không tồn tại :)" , 200);
    	}
    }
}
