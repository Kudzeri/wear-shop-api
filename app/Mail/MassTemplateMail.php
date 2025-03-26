<?php
namespace App\Mail;

use App\Models\MailTemplate;
use App\Models\Subscriber;
use Illuminate\Mail\Mailable;

class MassTemplateMail extends Mailable
{
    public MailTemplate $template;
    public Subscriber $subscriber;

    public function __construct(MailTemplate $template, Subscriber $subscriber)
    {
        $this->template = $template;
        $this->subscriber = $subscriber;
    }

    public function build(): self
    {
        $subject = $this->template->subject;
        $content = str_replace('[name]', $this->subscriber->name, $this->template->content);

        return $this->subject($subject)
                    ->markdown('emails.mass_template', ['content' => $content]);
    }
}
