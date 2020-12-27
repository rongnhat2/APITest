<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Consts;
use Session;
use DB;

class NewsfeedRepository extends BaseRepository implements RepositoryInterface
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
     *  Kiểm tra có item mới hay không
     *  
     *  Trả về True or False
    */
    public function hasNewItem($last_id, $index_id){
        return $last_id > $index_id ?  true : false;
    }



    /*
     *  lấy ra index_id
     *  
     *  Trả về Index_id
    */
    public function getIndexID($customer_id){
        $index_id = $this->model->where('customer_id', $customer_id)->first()->index_id;
        return $index_id;
    }

    /*
     *  setup index_id
     *  
     *  Trả về Index_id
    */
    public function setIndexID($customer_id, $index_id){
        $index_id = $this->model->where('customer_id', $customer_id)->update([
                'index_id'        =>  $index_id,
            ]);
        return $index_id;
    }

    /*
     *  lấy ra prev_id
     *  
     *  Trả về prev_id
    */
    public function getPrevID($customer_id){
        $prev_id = $this->model->where('customer_id', $customer_id)->first()->prev_id;
        return $prev_id;
    }

    /*
     *  setup prev_id
     *  
     *  Trả về prev_id
    */
    public function setPrevID($customer_id, $prev_id){
        $prev_id = $this->model->where('customer_id', $customer_id)->update([
                'prev_id'        =>  $prev_id,
            ]);
        return $prev_id;
    }



}
