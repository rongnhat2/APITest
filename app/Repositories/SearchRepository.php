<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Consts;
use Session;
use DB;

class SearchRepository extends BaseRepository implements RepositoryInterface
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
     *  Lấy ra ID cuối cùng
     *  
     *  Trả về ID
    */
    public function getLastID(){
        return $this->model->latest()->first()->id;
    }


    /*
     *  Lấy ra danh sách tìm kiếm
     *  
     *  Trả về tìm kiếm
    */
    public function getSearch($id){
        return $this->model->where('id', $id)->first();
    }

    /*
     *  ghi lại lịch sử tìm kiếm
     *  
     *  Trả về ID
    */
    public function createSearch($customer_id, $search_value){
        try {
            DB::beginTransaction();
            $search = $this->model->create([
                'customer_id'       =>  $customer_id,
                'value'             =>  $search_value,
                "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
            ]);
            DB::commit();
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
        return $this->model->latest()->first()->id;
    }

    /*
     *  xóa lịch sử tìm kiếm
     *  
     *  Trả về ID
    */
    public function deleteSearch($customer_id, $id){
        try {
            DB::beginTransaction();
            $this->model->where('customer_id', $customer_id)->where('id', $id)->delete();
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            return false;
            DB::rollBack();
        }
    }

}
