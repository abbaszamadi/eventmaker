<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public $userId;
    public function authenticate($request)
    {

        if( $request->hasHeader('authorization') )
        {
            $token = $request->header('authorization');
            $token = str_replace('Bearer ', '', $token);
            $userModel = new User();
            if (isset($token) and is_string($token)) {
                $user = $userModel->get(array('filters' => array('api_token' => $token)));
                if ($user) {
                    if ($user->statusCode == 0) {
                        $response = array(
                            'resultCode'    => 200,
                            'message'       => 'حساب کاربری غیر فعال است'
                        );
                        return $this->sendResponse($response);
                    }
                    $this->userId = $user->id;
                }else{
                    $response = array(
                        'resultCode'    => 400,
                        'message'       => 'اطلاعات ارسالی نامعتبر است'
                    );
                    return $this->sendResponse($response);
                }
            }else{
                $response = array(
                    'resultCode'    => 400,
                    'message'       => 'اطلاعات ارسالی نامعتبر است'
                );
                return $this->sendResponse($response);
            }
        }else{
            $response = array(
                'resultCode'    => 400,
                'message'       => 'اطلاعات ارسالی نامعتبر است'
            );
            return $this->sendResponse($response);
        }
    }
    public function sendResponse($data=array())
    {
        $resultCode = isset($data['resultCode'])? $data['resultCode'] : 200;
        $message    = isset($data['message']) ? $data['message'] : '';
        $data       = isset($data['data']) ? $data['data'] : array();
        $response   = [
            'resultCode'    => $resultCode,
            'data'          => $data,
            'message'       => $message,
        ];
        return response()->json($response, $resultCode,  [], JSON_UNESCAPED_UNICODE)->send();
    }


}
