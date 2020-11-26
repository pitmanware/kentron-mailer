<?php

namespace Kentron\Facade\Mail;

use Kentron\Facade\Mail\Template\AMail;

final class PhpMailer extends AMail
{
    public function send (string $to, string $subject, string $body = ""): bool
    {
        return false;
    }
}
