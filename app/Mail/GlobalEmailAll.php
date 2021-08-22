<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class GlobalEmailAll extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public  $content,$counter;
    public function __construct($subject, $content , $counter = [])
    {
        $this->content = $content;
        $this->subject = $subject;
        $this->counter = $counter;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->from('abc@abc.se', 'Anonymous');

        return $this->markdown('emails.system.index')->with(['content' => $this->content]);
    }
}
