<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Consts;
use Session;
use DB;

class PostRepository extends BaseRepository implements RepositoryInterface
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


    /*
     *  Tạo bài post mới
     *  
     *  Trả về ID bài post
    */
    public function createPost($customer_id, $request){
        $description    = $request->description;
        $state          = $request->state;
        try {
            DB::beginTransaction();
            $post = $this->model->create([
                'customer_id'       =>  $customer_id,
                'description'       =>  $description,
                'state'             =>  $state,
                'can_comment'       =>  '1',
                'is_banner'         =>  '0',
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return $post->id;
    }


    /*
     *  cập nhật thông tin, trạng thái bài post
     *  
     *  Trả về bài post
    */
    public function updatePost($post_id, $request){
        $description    = $request->description;
        $state          = $request->state;
        try {
            DB::beginTransaction();
            $post = $this->model->where('id', $post_id)->update([
                'description'       =>  $description,
                'state'             =>  $state,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return $post;
    }

    /*
     *  xóa bài viết, trạng thái bài post
     *  
     *  Trả về true, false
    */
    public function deletePost($post_id){
        try {
            DB::beginTransaction();
            $post = $this->model->where('id', $post_id)->delete();
            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return $post;
    }

    /*
     *  lấy ra comment cho bài post
     *  
     *  Trả về comment
    */
    public function getPostComment($post_id){
        $comment  = $this->model->where('post_id', $post_id)->with('customer')->get();
        return $comment;
    }

    /*
     *  Tạo comment cho bài post
     *  
     *  Trả về true, false
    */
    public function createPostComment($customer_id, $post_id, $request){
        $comment  = $request->comment;
        try {
            DB::beginTransaction();
            $comment = $this->model->create([
                'post_id'       =>  $post_id,
                'customer_id'   =>  $customer_id,
                'comment'       =>  $comment,
                "created_at"    =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"    => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return $comment->id;
    }

    /*
     *  Gửi báo cáo bài post không hợp lệ
     *  
     *  Trả về true, false
    */
    public function sendReport($customer_id, $post_id, $report_value){
        try {
            DB::beginTransaction();
            $report = $this->model->create([
                'post_id'       =>  $post_id,
                'customer_id'   =>  $customer_id,
                'report_value'  =>  $report_value,
                "created_at"    =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"    => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return true;
    }



    /*
     *  lấy ra danh sách customer like post
     *  
     *  Trả về true, false
    */
    public function getListLike($post_id){
        return $this->model->where('post_id', $post_id)->get();
    }

    /*
     *  check user đã like bài viết hay chưa
     *  
     *  Trả về true, false
    */
    public function checkLike($customer_id, $post_id){
        $is_like = $this->model->where('customer_id', $customer_id)->where('post_id', $post_id)->first();
        if ($is_like == null) {
           return false;
        }else{
            return true;
        }
    }
    /*
     *  Like bài viết
     *  
     *  Trả về true, false
    */
    public function setLike($customer_id, $post_id){
        try {
            DB::beginTransaction();
            $like = $this->model->create([
                'post_id'       =>  $post_id,
                'customer_id'   =>  $customer_id,
                "created_at"    =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"    => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return true;
    }

    /*
     *  bỏ Like bài viết
     *  
     *  Trả về true, false
    */
    public function setUnLike($customer_id, $post_id){
        try {
            DB::beginTransaction();
            $unlike = $this->model->where('post_id', $post_id)->where('customer_id', $customer_id)->delete();
            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return true;
    }


    /*
     *  Tạo URL Media Image cho bài post
     *  
     *  Trả về ID bài post
    */
    public function createPostImage($post_id, $request){
        $image          = $request->image;
        try {
            DB::beginTransaction();

            foreach ($image as $key => $url) {
                $this->model->create([
                    'post_id'       => $post_id,
                    'url'           => $url,
                    "created_at"    =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                    "updated_at"    => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                ]);
            }

            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return true;
    }
    /*
     *  Tạo URL Media Video cho bài post
     *  
     *  Trả về ID bài post
    */
    public function createPostVideo($customer_id, $request){
        $video          = $request->video;
        try {
            DB::beginTransaction();

            foreach ($video as $key => $url) {
                $this->model->create([
                    'post_id'       => $post_id,
                    'url'           => $url,
                    "created_at"    =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                    "updated_at"    => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                ]);
            }

            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return true;
    }

    /*
     *  Xóa URL Media Image cho bài post
     *  
     *  Trả về ID bài post
    */
    public function deleteImagePost($post_id){
        try {
            DB::beginTransaction();
            $this->model->where('post_id', $post_id)->delete();
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return true;
        }
    }
    /*
     *  Xóa URL Media Video cho bài post
     *  
     *  Trả về ID bài post
    */
    public function deleteVideoPost($post_id){
        try {
            DB::beginTransaction();
            $this->model->where('post_id', $post_id)->delete();
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return true;
        }
    }
    /*
     *  Xóa Banner cho bài post
     *  
     *  Trả về ID bài post
    */
    public function deleteBannerPost($post_id){
        try {
            DB::beginTransaction();
            $this->model->where('post_id', $post_id)->delete();
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return true;
        }
    }

    /*
     *  ban user khỏi bài post
     *  
     *  Trả về true
    */
    public function createBanner($banner, $post_id){
        try {
            DB::beginTransaction();

            foreach ($banner as $key => $customer_id) {
                $this->model->create([
                    'post_id'       => $post_id,
                    'customer_id'   => $customer_id,
                    "created_at"    =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                    "updated_at"    => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                ]);
            }

            DB::commit();
        } catch (\Exception $exception) {
            return "false";
            DB::rollBack();
        }
    }

    // lấy ra id người viết post
    public function getPostCustomerID($post_id){
        $post_data  = $this->model->where('id', $post_id)->first();
        return $post_data->customer_id;
    }

    // Lấy data bài viết mới
    public function getPostData($customer_id, $post_id){
        $post_data  = $this->model->with('image')->with('video')->where('id', $post_id)->first();
        return $post_data;
    }
    // Post bị Xóa?
    public function postHasRemove($post_id){
        $post_data  = $this->model->where('id', $post_id)->first();
        // post bị banner
        $is_remove  = false;  
        // kiểm tra banner
        if ($post_data->state == -1) {
            $is_remove  = true;  
        }
        return $is_remove;
    }
    // Post bị ban?
    public function postHasBanner($post_id){
        $post_data  = $this->model->where('id', $post_id)->first();
        // post bị banner
        $is_banner  = false;  
        // kiểm tra banner
        if ($post_data->is_banner == 1) {
            $is_banner  = true;  
        }
        return $is_banner;
    }
    // bạn là chủ post ?
    public function postIsUses($customer_id, $post_id){
        $post_data  = $this->model->where('id', $post_id)->first();
        //  bạn là chủ post ?
        $is_uses  = false;  
        // kiểm tra  bạn là chủ post ?
        if ($post_data->customer_id == $customer_id) {
            $is_uses  = true;  
        }
        return $is_uses;
    }
    // bạn bị block khỏi post
    public function postIsBlock($customer_id, $post_id){
        $post_banner    = $this->model->where('post_id', $post_id)->get();
        // kiểm tra bạn có bị banner không
        $is_banner      = false;
        foreach ($post_banner as $key => $value) {
            if ($value->customer_id == $customer_id) {
                $is_banner = true;
                return $is_banner;
            }
        }
        return $is_banner;
    }
    // lấy ra ID cuối cùng của newsfeed
    public function getLastID(){
        return $this->model->latest()->first()->id;
    }



    /*
     *  lấy ra những item mới
     *  
     *  Trả về True or False
    */
    public function getNewItem($index_id, $next_id, $item_const){
        $new_item = $this->model->where('id', '>', $index_id)->where('id', '<=', $next_id)->with('customer')->take($item_const)->get();
        return $new_item;
    }
    /*
     *  lấy ra những item cũ
     *  
     *  Trả về True or False
    */
    public function getPrevItem($prev_id, $prev_load_id, $item_const){
        $prev_item = $this->model->where('id', '>=', $prev_load_id)->where('id', '<', $prev_id)->with('customer')->take($item_const)->get();
        return $prev_item;
    }

    /*
     *  tìm theo Post
    */
    public function postSearch($search_value){
        return $this->model->where('description', 'like', '%'.$search_value.'%')->take(5)->get();
    }
}
