<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function sendResponse($data=array())
    {
        $resultCode = isset($data['resultCode'])? $data['resultCode'] : 200;
        $message    = isset($data['message']) ? $data['message'] : '';
        $data       = isset($data['data']) ? $data['data'] : array();
        $response = [
            'resultCode'    => $resultCode,
            'data'          => $data,
            'message'       => $message,
        ];
        return response()->json($response, $resultCode,  [], JSON_UNESCAPED_UNICODE);
    }


}
