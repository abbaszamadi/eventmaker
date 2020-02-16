<?php

namespace App\Http\Controllers;

use App\Event;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{

    /**
     * events that user invited to.
     *
     * @return array of object of events
     */

    public function invited_events(Request $request)
    {
        $this->authenticate($request);
        $eventModel      = new Event();
        $filters = array('i.userId' => $this->userId);
        $fields  = array('e.*');
        $events = $eventModel->allJoined(array('filters' => $filters, 'fields' => $fields));
        $response = array(
            'resultCode'    => 200,
            'message'       => '',
            'data'          => array('events' => $events)
        );
        return $this->sendResponse($response);
    }


    /**
     * all events that user created.
     *
     * @return array of object of events
     */
    public function user_events(Request $request)
    {
        $this->authenticate($request);
        $eventModel      = new Event();
        $filters = array('userId' => $this->userId);
        $events = $eventModel->all(array('filters' => $filters));
        $response = array(
            'resultCode'    => 200,
            'message'       => '',
            'data'          => array('events' => $events)
        );
        return $this->sendResponse($response);
    }



    public function upload_avatar(Request $request)
    {
        $this->authenticate($request);
        $userModel  = new User();
        $validator  = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        if($validator->fails()){
            $response = array(
                'resultCode'    => 405,
                'message'       => $validator->errors()
            );
            return $this->sendResponse( $response );
        }
        $user = $userModel->get(array('filters' => array('id' => $this->userId)));
        if (isset($user->avatar))
        {
            if(File::exists($user->avatar)) {
                File::delete($user->avatar);
            }
        }
        $year       = date("Y", time());
        $month      = date("m");
        $day        = date('d', time());

        $imageName      = time().'.'.request()->avatar->getClientOriginalExtension();
        $imagePath      = 'avatars' . DIRECTORY_SEPARATOR;
        $imagePath     .= $year . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $day . DIRECTORY_SEPARATOR;
        $uploadResult   = request()->avatar->move(public_path($imagePath), $imageName);
        $filters = array('id' => $this->userId);
        $data    = array('avatar' => public_path($imagePath . $imageName));
        if ( $userModel->update(array('filters' => $filters, 'data' => $data)))
        {
            $response = array(
                'resultCode'    => 200,
                'message'       => 'عکس پروفایل با موفقیت ویرایش شد'
            );
        }else{
            $response = array(
                'resultCode'    => 400,
                'message'       => 'خطا در آپدیت عکس پروفایل'
            );
        }
        return $this->sendResponse($response);
    }
}
