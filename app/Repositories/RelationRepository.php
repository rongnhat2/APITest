<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Consts;
use Session;
use DB;

class RelationRepository extends BaseRepository implements RepositoryInterface
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
     *  Lấy ra danh sách relation của customer
     *  
     *  Trả về Danh sách ID relation
    */
    public function getListRelation($customer_id){
        return $this->model->where('customer_id_01', $customer_id)->get();
    }

    /*
     *  Kiểm tra đã có relation chưa
     *  
     *  Trả về True hoặc False
    */
    public function checkHasRelation($list_friend, $user_id){
        $has_relation = false;
        foreach ($list_friend as $key => $value) {
            if ($value->customer_id_02 == $user_id) {
                $has_relation = true;
                return $has_relation;
            }
        }
        return $has_relation;
    }

    /*
     *  Kiểm tra đã là bạn bè ?
     *  
     *  Trả về True hoặc False
    */
    public function checkHasFriend($customer_id, $user_id){
        $relation = $this->model->where('customer_id_01', $customer_id)->where('customer_id_02', $user_id)->where('relation_value', 1)->first();
        if ($relation == null) {
           return false;
        }else{
            return true;
        }
    }

    /*
     *  Lấy ra danh sách Friend của customer
     *  
     *  Trả về Danh sách ID Friend
    */
    public function getListFriend($customer_id){
        return $this->model->where('customer_id_01', $customer_id)->where('relation_value', 1)->with('customer_02')->get();
    }


    /*
     *  Truyền vào user_id
     *  
     *  Trả về danh sách friend bạn đã add
    */
    public function getAddFriendRequest($customer_id){
        return $this->model->where('customer_id_01', $customer_id)->with('customer_02')->get();
    }

    /*
     *  Truyền vào user_id
     *  
     *  Trả về danh sách friend bạn được gửi lời mời add
    */
    public function getAddFriendResponse($customer_id){
        return $this->model->where('customer_id_02', $customer_id)->with('customer_01')->get();
    }



    /*
     *  Tạo relation
     *  
     *  Trả về True hoặc False
    */
    public function createRelation($customer_id, $user_id){
        try {
            DB::beginTransaction();

            $relation = $this->model->create([
                'customer_id_01'        =>  $customer_id,
                'customer_id_02'        =>  $user_id,
                'relation_value'        =>  '0',
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
     *  chấp nhận relation
     *  
     *  Trả về True hoặc False
    */
    public function acceptRelation($customer_id, $user_id){
        try {
            DB::beginTransaction();

            $acceptFriend = $this->model->where('customer_id_01', $user_id)->where('customer_id_02', $customer_id)->update([
                'relation_value'        =>  '1',
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);

            $createFriend = $this->model->create([
                'customer_id_01'        =>  $customer_id,
                'customer_id_02'        =>  $user_id,
                'relation_value'        =>  '1',
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



}
