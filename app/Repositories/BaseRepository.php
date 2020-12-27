<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Hash;

abstract class BaseRepository implements RepositoryInterface
{
    // model property on class instances
    protected $model;

    // Constructor to bind model to repo
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // Get all instances of model
    public function getAll()
    {
        return $this->model->all()->sortByDesc("id");
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

    public function updateOrCreate(array $data, $id)
    {
        return $this->model->updateOrCreate(['id' => $id], $data);
    }

    // trả về thông báo và mã
    public function sendResponse($message, $code)
    {
        $res = [
            'code' => $code,
            'message' => $message,
        ];
        return response()->json($res);
    }

    // trả về thông báo và mã
    public function sendResponseWithData($message, $code, $data)
    {
        $res = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
        return response()->json($res);
    }

    /*
     *  Kiểm tra token có hợp lệ
     *  
     *  input   : token của customer
     *  output  : Nếu đúng trả về true, sai trả về false
     *  
    */
    public function checkToken($token){

        // lấy ra id người dùng và token của client
        list($id_token, $db_token) = explode('$', $token, 2);
        if ($id_token == null || $db_token == null) {
            return false;
        }else{
            // lấy ra id cuối cùng
            $last_id = $this->model->latest('id')->first()->id;
            if ($id_token > $last_id) {
                return false;
            }else{
                // key trong DB
                $secret_key     = $this->model->findOrFail($id_token)->secret_key;

                // token trong DB
                $token_key      = $this->model->findOrFail($id_token)->token;
                // echo $token_key;

                // check token trên client
                $checkClientHash = Hash::check($id_token . '$' . $secret_key, $db_token);

                // thừa vãi lồn nhưng đề yêu cầu... generate 2 chuỗi như nhau thì giải mã kiểu L gì chả như nhau
                $checkDatabaseHash = Hash::check($id_token . '$' . $secret_key, $token_key);

                if ($checkClientHash && $checkDatabaseHash) {
                    return $id_token;
                }else{
                    return false;
                }
            }
        }
    }
    // lấy ra ID cuối cùng của newsfeed
    public function getLastID(){
        return $this->model->latest()->first()->id;
    }

}
