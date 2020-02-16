<?php

namespace App\Http\Controllers;

use App\Event;
use App\Helpers\Sanitization;
use App\Invitation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventController extends Controller
{

    public function create(Request $request)
    {


        $this->authenticate($request);
        $userModel  = new User();
        $eventModel = new Event();
        $invitationModel = new Invitation();
        $validator  = Validator::make($request->all(), [
            'subject'       => 'required|max:255',
            'description'   => 'required|max:512',
            'invitedUsers'  => 'required'
        ]);
        if($validator->fails()){
            $response = array(
                'resultCode'    => 405,
                'message'       => $validator->errors()
            );
            return $this->sendResponse( $response );
        }
        $subject        = $request->get('subject');
        $description    = $request->get('description');
        $eventTime      = $request->get('eventTime');
        $eventCode      = Str::random(8);
        $invitedUsers   = $request->get('invitedUsers');
        $invitedUsers = json_decode($invitedUsers);
        $eventData      = array(
            'subject'       => $subject,
            'description'   => $description,
            'eventTime'     => $eventTime,
            'eventCode'     => $eventCode,
            'userId'        => $this->userId,
            'createdAt'     => time(),
        );
        if ( $eventId = $eventModel->save($eventData) )
        {
            foreach ($invitedUsers as $invitedUser)
            {
                if(filter_var( $invitedUser, FILTER_VALIDATE_EMAIL ))
                {
                    $filters = array('email' => $invitedUser);
                    $user    = $userModel->get(array('filters' => $filters));
                }else{
                    if (Sanitization::isValidPhone($invitedUser))
                    {
                        $invitedUser    = Sanitization::sanitizePhone($invitedUser);
                        $filters        = array('phone' => $invitedUser);
                        $user           = $userModel->get(array('filters' => $filters));
                    }
                }

                if (isset($user))
                {
                    $inviteData = array(
                        'userId'    => $user->id,
                        'eventId'   => $eventId,
                        'createdAt' => time()
                    );
                    $invitations[] = $inviteData;
                }
            }
            if (isset($invitations))
            {
                if ( $invitationModel->saveBatch($invitations) )
                {

                    $response = array(
                        'resultCode'    => 200,
                        'message'       => 'رویداد با موفقیت ایجاد شد'
                    );
                }else{
                    $response = array(
                        'resultCode'    => 400,
                        'message'       => 'خطا در دعوت از کاربران به رویداد!'
                    );
                }
            }else{
                $response = array(
                    'resultCode'    => 200,
                    'message'       => 'رویداد با موفقیت ایجاد شد. کاربران دعوت شده ای وجود ندارد'
                );
            }
        }else{
            $response = array(
                'resultCode'    => '',
                'message'       => 'خطا در ذخیره ی رویداد!'
            );
        }
        return $this->sendResponse($response);
    }

    public function view()
    {

    }
    public function update()
    {

    }


}
