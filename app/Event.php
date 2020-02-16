<?php

namespace App;


use Illuminate\Support\Facades\DB;

class Event extends Base
{
    public $table = 'events';


    public function allJoined($data)
    {
        $fields     = isset($data['fields'])? $data['fields'] : "*";
        $filters    = isset($data['filters'])? $data['filters'] : array();
        return DB::table("$this->table as e")
            ->join('invitations AS i', 'e.id', '=', 'i.eventId')
            ->join('users AS u', 'u.id', '=', 'i.userId')
            ->select($fields)
            ->where($filters)
            ->groupBy('e.id')
            ->get();
    }
}



