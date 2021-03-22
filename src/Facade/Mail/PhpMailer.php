<?php

namespace Kentron\Facade\Mail;

use Kentron\Facade\Mail\Entity\Attachment\MailAttachmentEntity;
use Kentron\Facade\Mail\Entity\Embed\MailEmbedEntity;
use Kentron\Facade\Mail\Entity\MailTargetEntity;
use Kentron\Facade\Mail\Entity\MailTransportEntity;
use Kentron\Facade\Mail\Template\AMail;

use PHPMailer\PHPMailer\PHPMailer as Mailer;

final class PhpMailer extends AMail
{
    /**
     * {@inheritDoc}
     *
     * @var Mailer
     */
    protected static $mailer;

    public static function send (MailTransportEntity $mailTransportEntity): bool
    {
        self::$mailer = new Mailer();
        self::$mailer->Host = $mailTransportEntity->getHost();
        self::$mailer->Port = $mailTransportEntity->getPort();

        self::$mailer->SMTPSecure = Mailer::ENCRYPTION_STARTTLS;
        self::$mailer->SMTPAuth = true;

        self::$mailer->Username = $mailTransportEntity->getUsername();
        self::$mailer->Password = $mailTransportEntity->getPassword();

        // Add recipients
        foreach ($mailTransportEntity->getTargetCollectionEntity()->iterateEntities() as $targetEntity) {
            self::addRecipient($targetEntity);
            $mailTransportEntity->addError($targetEntity->getErrors());
        }

        // Add attchments
        foreach ($mailTransportEntity->getAttachmentCollectionEntity()->iterateEntities() as $attachmentEntity) {
            self::attach($attachmentEntity);
            $mailTransportEntity->addError($attachmentEntity->getErrors());
        }

        // Add embeds
        foreach ($mailTransportEntity->getEmbedCollectionEntity()->iterateEntities() as $embedEntity) {
            self::embed($embedEntity);
            $mailTransportEntity->addError($embedEntity->getErrors());
        }

        self::$mailer->setFrom($mailTransportEntity->getFromEmail(), $mailTransportEntity->getFromName());
        self::$mailer->isHTML($mailTransportEntity->getIsHtml());

        self::$mailer->Subject = $mailTransportEntity->getSubject();
        self::$mailer->Body = $mailTransportEntity->getBody();
        self::$mailer->AltBody = $mailTransportEntity->getAltBody();

        try {
            $sendSuccess = self::$mailer->send();
        }
        catch (\Throwable $th) {
            $mailTransportEntity->addError($th->getMessage());
        }
        finally {
            if (!$sendSuccess) {
                $mailTransportEntity->addError(self::$mailer->ErrorInfo);
                return false;
            }
        }

        $mailTransportEntity->incrementSentCount();

        return true;
    }

    protected static function addRecipient (MailTargetEntity $mailTargetEntity): bool
    {
        try {
            $addRecipientSuccess = self::$mailer->addAddress(
                $mailTargetEntity->getEmail(),
                $mailTargetEntity->getName()
            );
        }
        catch (\Throwable $th) {
            $mailTargetEntity->addError($th->getMessage());
            return false;
        }

        return $addRecipientSuccess;
    }

    protected static function attach (MailAttachmentEntity $mailAttachmentEntity): bool
    {
        try {
            $attachSuccess = self::$mailer->addAttachment(
                $mailAttachmentEntity->getPath(),
                $mailAttachmentEntity->getName()
            );
        }
        catch (\Throwable $th) {
            $mailAttachmentEntity->addError($th->getMessage());
            return false;
        }

        return $attachSuccess;
    }

    protected static function embed (MailEmbedEntity $mailEmbedEntity): bool
    {
        try {
            $embedSuccess = self::$mailer->addEmbeddedImage(
                $mailEmbedEntity->getPath(),
                $mailEmbedEntity->getCid(),
                $mailEmbedEntity->getName()
            );
        }
        catch (\Throwable $th) {
            $mailEmbedEntity->addError($th->getMessage());
            return false;
        }

        return $embedSuccess;
    }
}
