<?php

namespace App\Http\Controllers;

use App\Event;
use App\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{


    public function update(Request $request)
    {
        $this->authenticate($request);
        $eventModel      = new Event();
        $invitationModel = new Invitation();
        $validator       = Validator::make($request->all(), [
            'eventId'           => 'required',
            'presenceStatus'    => 'required',
        ]);
        if($validator->fails()){
            $response = array(
                'resultCode'    => 405,
                'message'       => $validator->errors()
            );
            return $this->sendResponse( $response );
        }
        $eventId        = $request->get('eventId');
        $presenceStatus = $request->get('presenceStatus');
        $filters        = array('id' => $eventId);
        $event          = $eventModel->get(array('filters' => $filters));
        if (isset($event))
        {
            if ( time() < $event->eventTime )
            {
                $filters = array('userId' => $this->userId, 'eventId' => $eventId);
                $invitation = $invitationModel->get(array('filters'));
                if (isset($invitation))
                {
                    $invitationData = array('presenceStatus' => $presenceStatus);
                    if ( $invitationModel->update(array('filters' => $filters, 'data' => $invitationData)) )
                    {
                        $response = array(
                            'resultCode'    => 200,
                            'message'       => 'دعوت نامه با موفقیت ویرایش شد'
                        );
                    }else{
                        $response = array(
                            'resultCode'    => 400,
                            'message'       => 'اطلاعات جدیدی برای ویرایش وجود ندارد'
                        );
                    }
                }else{
                    $response = array(
                        'resultCode'    => 400,
                        'message'       => 'اطلاعات ارسالی نامعتبر است'
                    );
                }
            }else{
                $response = array(
                    'resultCode'    => 400,
                    'message'       => 'زمان دعوت نامه منقضی شده است'
                );
            }
        }else{
            $response = array(
                'resultCode'    => 400,
                'message'       => 'اطلاعات ارسالی نامعتبر است'
            );
        }
        return $this->sendResponse($response);
    }
}
