<?php

namespace Kentron\Facade\Mail;

use Kentron\Facade\Mail\Template\AMail;

use Swift_Image;
use Swift_SmtpTransport;
use Swift_Message;
use Swift_Mailer;

final class SwiftMailer extends AMail
{
    /**
     * The content of the email
     * @var Swift_Message
     */
    private $message;

    /**
     * The mailer transport
     * @var Swift_SmtpTransport
     */
    private $transport;

    public function __construct ()
    {
        $this->message = new Swift_Message();
    }

    public function send (string $to, string $subject, string $body = ""): bool
    {
        if (is_null($this->transport)) {
            $this->buildTransport();
        }

        try {
            $this->message->setFrom([$this->fromEmail => $this->fromName]);
            $this->message->setTo($to);
            $this->message->setSubject($subject);

            $this->message->setBody($body);
            $this->message->setContentType($this->contentType);

            $mailer = new Swift_Mailer($this->transport);
            $this->mailSentCount = $mailer->send($this->message);
        }
        catch (\Exception $ex) {
            $this->errors[] = $ex->getMessage();
            return false;
        }

        return true;
    }

    private function buildTransport (): void
    {
        $this->transport = new Swift_SmtpTransport($this->smtp, $this->port, $this->method);

        $this->transport->setUsername($this->username);
        $this->transport->setPassword($this->password);
    }

    public function embedImage (string $imagePath): string
    {
        return $this->message->embed(Swift_Image::fromPath($imagePath));
    }

}
