<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Consts;
use Session;
use DB;

class MessageRepository extends BaseRepository implements RepositoryInterface
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
     *  block message người dùng
    */
    public function setBlock($customer_id, $user_id){
        try {
            DB::beginTransaction();

            $relation = $this->model->create([
                'customer_id_01'        =>  $customer_id,
                'customer_id_02'        =>  $user_id,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
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
     *  bỏ block message người dùng
    */
    public function setUnBlock($customer_id, $user_id){
        try {
            DB::beginTransaction();
            $this->model->where('customer_id_01', $customer_id)->where('customer_id_02', $user_id)->delete();
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return true;
        }
    }

    /*
     *  Kiểm tra người dùng đã có conversation với user_id hay chưa
    */
    public function checkHasConversaation($customer_id, $user_id){
        $is_conversation = $this->model->where('customer_id_01', $customer_id)->where('customer_id_02', $user_id)->first();
        if ($is_conversation == null) {
           return false;
        }else{
            return true;
        }
    }

    /*
     *  tạo conversation
    */
    public function createConversation($customer_id, $user_id){
        try {
            DB::beginTransaction();

            $conversation = $this->model->create([
                'conversation_code' =>  mt_rand(1000000, 9999999),
                'customer_id_01'    =>  $customer_id,
                'customer_id_02'    =>  $user_id,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return $conversation;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
        return $conversation;
    }
    /*
     *  tạo conversation cho user đối thủ
    */
    public function createUserConversation($customer_id, $user_id, $conversation_code){
        try {
            DB::beginTransaction();

            $conversation = $this->model->create([
                'conversation_code' =>  $conversation_code,
                'customer_id_01'    =>  $customer_id,
                'customer_id_02'    =>  $user_id,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return $conversation;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
        return $conversation;
    }
    /*
     *  xóa conversation cho user đối thủ
    */
    public function deleteUserConversation($customer_id, $user_id, $conversation_code){
        try {
            DB::beginTransaction();

            $conversation = $this->model->where('conversation_code', $conversation_code)->where('customer_id_01', $customer_id)->where('customer_id_02', $user_id)->delete();
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
        return true;
    }



    /*
     *  lấy conversation của customer
    */
    public function getConversation($customer_id){
        return  $this->model->where('customer_id_01', $customer_id)->get();
    }


    /*
     *  lấy conversation của customer
    */
    public function getMessage($customer_id, $user_id){
        return $this->model->where('customer_id_01', $customer_id)->where('customer_id_02', $user_id)->first();
    }

    /*
     *  lấy ra danh sách tin nhắn trong message
    */
    public function getMessageOfConver($conversation_code){
        return $this->model->where('conversation_code', $conversation_code)->get();
    }

    /*
     *  tạo message với customer
    */
    public function createMessage($customer_id, $message, $conversation_code){
        try {
            DB::beginTransaction();

            $conversation = $this->model->create([
                'conversation_code' =>  $conversation_code,
                'customer_id'       =>  $customer_id,
                'message'           =>  $message,
                'status'            =>  '0',
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            echo $exception;
            DB::rollBack();
            return false;
        }
        return true;
    }
    /*
     *  chuyển trạng thái đã xem message với customer
    */
    public function updateMessage($conversation_code, $customer_id){
        try {
            DB::beginTransaction();

            $read_all = $this->model->where('conversation_code', $conversation_code)->where('customer_id', $customer_id)->update([
                'status'            =>  '1',
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            echo $exception;
            DB::rollBack();
            return false;
        }
        return true;
    }
    /*
     *  chuyển trạng thái đã xem message với customer
    */
    public function deleteMessage($conversation_code, $customer_id, $message_id){
        try {
            DB::beginTransaction();

            $read_all = $this->model->where('conversation_code', $conversation_code)->where('customer_id', $customer_id)->where('id', $message_id)->update([
                'status'            =>  '-1',
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            echo $exception;
            DB::rollBack();
            return false;
        }
        return true;
    }


}
