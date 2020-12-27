<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Consts;
use Session;
use DB;

class QuantityRepository extends BaseRepository implements RepositoryInterface
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

    
    // lấy ra danh sách số lượng của sản phẩm theo size
    public function getQuantity($id)
    {
        $color = $this->model->where('item_color', $id)->with(['color' => function ($q){
                $q->with('item');
            }]
        )->get();
        return $color;
    }

    // Thêm dối tượng theo màu
    public function createModuleQuantity($id)
    {
        $size = array("XS", "S", "M", "L", "XL");
        try {
            DB::beginTransaction();

            foreach ($size as $key => $value) {
                $this->model->create([
                    'item_color'        =>  $id,
                    'size'              =>  $value,
                    'quantity'          =>  "0",
                    "created_at"        =>  \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                    "updated_at"        => \Carbon\Carbon::now('Asia/Ho_Chi_Minh'),
                ]);
            }

            Session::flash('success', 'Thêm thành công');
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            // dd($exception);
            Session::flash('error', 'Đã có lỗi sảy ra');
            DB::rollBack();
            return true;
        }
    }

    // lấy ra danh sách số lượng của sản phẩm theo size
    public function updateQuantity($request, $id, $c_id)
    {
        try {
            DB::beginTransaction();
            // lấy số lượng hiện có trong kho
            $item_data = $this->model->where('item_color', '=', $c_id)->get();
            $old_quantity = array();
            foreach ($item_data as $key => $value) {
                $old_quantity[$key] = $value->quantity;
            }

            // cập nhật vòng lặp 5 size của sản phẩm XS, S, M, L, XL
            $size_data = array("XS", "S", "M", "L", "XL");
            $size       = $request->size;
            $quantity   = $request->quantity;
            foreach ($size_data as $key => $value) {
                $this->model->where('item_color', '=', $c_id)->where('size', '=', $size_data[$key])->update([
                    'quantity'          =>  $old_quantity[$key] + $quantity[$key],
                ]);
            }
            DB::commit();

            Session::flash('success', 'Thêm thành công');
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            dd($exception);
            Session::flash('error', 'Đã có lỗi sảy ra');
            DB::rollBack();
            return true;
        }
        return $color;
    }

}
