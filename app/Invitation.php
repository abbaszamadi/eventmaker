<?php

namespace App;


use Illuminate\Support\Facades\DB;

class Invitation extends Base
{
    public $table = 'invitations';



    public function allJoined($data)
    {
        $fields     = isset($data['fields'])? $data['fields'] : "*";
        $filters    = isset($data['filters'])? $data['filters'] : array();
        return DB::table("$this->table as i")
            ->join('events AS e', 'e.id', '=', 'i.eventId')
            ->join('users AS u', 'u.id', '=', 'i.userId')
            ->select($fields)
            ->where($filters)
            ->groupBy('u.id')
            ->get();
    }

}



