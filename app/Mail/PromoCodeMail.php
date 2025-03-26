<?php

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Mail\Mailable;

class PromoCodeMail extends Mailable
{
    public $subscriber;

    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function build(): self
    {
        return $this->subject('Твой персональный промокод')
            ->markdown('emails.promo_code');
    }
}
