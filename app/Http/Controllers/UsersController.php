<?php

namespace App\Http\Controllers;

use App\Event;
use App\User;
use Illuminate\Http\Request;
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




}
