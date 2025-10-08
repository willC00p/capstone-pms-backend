<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), 'PMS Admin')
                    ->subject('Your password reset code')
                    ->view('emails.reset_code')
                    ->with(['code' => $this->code]);
    }
}
