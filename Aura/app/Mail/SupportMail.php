<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subjectLine;
    public $messageText;

    public function __construct($user, $subjectLine, $messageText)
    {
        $this->user = $user;
        $this->subjectLine = $subjectLine;
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('[Soporte Aura] ' . $this->subjectLine)
                    ->markdown('emails.support');
    }
}
