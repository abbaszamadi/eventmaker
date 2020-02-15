<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data       = $this->data;
        $from       = isset($data['from']) ? $data['from'] : null;
        $subject    = isset($data['subject']) ? $data['subject'] : '';
        $viewName   = isset($data['viewName']) ? $data['viewName'] : '';
        return  $this->from($from)->subject($subject)->view($viewName)->with('data', $data);
    }
}
