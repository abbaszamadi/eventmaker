<?php

namespace App;


use Illuminate\Support\Facades\DB;

class Base
{
    public $table = null;
    public function get($data=array())
    {
        $fields     = isset($data['fields'])? $data['fields'] : "*";
        $filters    = isset($data['filters'])? $data['filters'] : array();
        return DB::table($this->table)->where($filters)->select($fields);
    }

    public function save($data)
    {
        return DB::table($this->table)->insertGetId($data);
    }

    public function update($data)
    {
        $filters    = isset($data['filters'])? $data['filters'] : array('id' => 0);
        $fields     = isset($data['data'])? $data['data'] : array();
        return DB::table($this->table)->where($filters)->update($fields);
    }


}
