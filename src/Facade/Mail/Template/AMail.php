<?php

namespace Kentron\Facade\Mail\Template;

use Kentron\Service\File;
use Kentron\Template\TError;

abstract class AMail
{
    use TError;

    public const ATTACHMENT_IMAGE = 1;

    /**
     * Count of all emails successfully delivered
     *
     * @var int
     */
    protected $mailSentCount;

    /**
     * Username for the mailer transport
     *
     * @var string
     */
    protected $username = "";

    /**
     * Password for the mailer transport
     *
     * @var string
     */
    protected $password = "";

    /**
     * SMTP domain
     *
     * @var string
     */
    protected $smtp = "smtp.gmail.com";

    /**
     * Port number
     *
     * @var int
     */
    protected $port = 465;

    /**
     * Mail protocol
     *
     * @var string
     */
    protected $method = "ssl";

    /**
     * Origin email address
     *
     * @var string
     */
    protected $fromEmail = "";

    /**
     * Origin display name
     *
     * @var string
     */
    protected $fromName = "";

    /**
     * Content type of the email body
     *
     * @var string
     */
    protected $contentType;

    /**
     * list of paths and CIDs of the files to be attached
     *
     * @var array
     */
    protected $attachments;

    /**
     * list of paths and CIDs of the files to be embedded
     *
     * @var array
     */
    protected $embeds;

    abstract public function send (string $to, string $subject, string $body = ""): bool;

    final public function attachFile (int $attachmentType, string $filePath)
    {
        if (!File::isValidFile($filePath)) {
            throw new \UnexpectedValueException("'{$filePath}' is not a valid file");
        }

        # TODO change this to entity collection
        $this->attachments[] = [
            "path" => $filePath
        ];

        switch ($attachmentType) {
            case self::ATTACHMENT_IMAGE:
                return $this->addImage($filePath);
                break;
        }
    }

    final public function embedFile (int $attachmentType, string $filePath)
    {
        if (!File::isValidFile($filePath)) {
            throw new \UnexpectedValueException("'{$filePath}' is not a valid file");
        }

        $this->attachmentPath = $filePath;

        switch ($attachmentType) {
            case self::ATTACHMENT_IMAGE:
                return $this->addImage($filePath);
                break;
        }
    }

    /**
     *
     * Methods
     *
     */
    final public function setUsername (string $username): void
    {
        $this->username = $username;
    }

    final public function setPassword (string $password): void
    {
        $this->password = $password;
    }

    final public function setSmtp (string $smtp): void
    {
        $this->smtp = $smtp;
    }

    final public function setPort (int $port): void
    {
        $this->port = $port;
    }

    final public function setMethod (string $method): void
    {
        $this->method = $method;
    }

    final public function setFromEmail (string $fromEmail): void
    {
        $this->fromEmail = $fromEmail;
    }

    final public function setFromName (string $fromName): void
    {
        $this->fromName = $fromName;
    }

    final public function setContentType (string $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * Helpers
     */

    public function addImage (string $filePath): void {}
}
