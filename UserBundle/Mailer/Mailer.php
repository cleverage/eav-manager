<?php

namespace CleverAge\EAVManager\UserBundle\Mailer;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Exception;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Twig_Environment;
use UnexpectedValueException;

class Mailer // implements \FOS\UserBundle\Mailer\MailerInterface
{
    /** @var Swift_Mailer */
    protected $mailer;

    /** @var array */
    protected $sender;

    /** @var Twig_Environment */
    protected $twig;

    public function __construct(Swift_Mailer $mailer, $sender, Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->sender = $sender;
        $this->twig = $twig;
    }

    public function sendAdminResetPasswordEmailMessage(User $user, $password)
    {
        $parameters = [
            'user' => $user,
            'password' => $password,
        ];

        $this->sendMail('CleverAgeEAVManagerUserBundle:Mail:user.reset_password.success.html.twig', $user, $parameters);
    }

    /**
     * @param string $template
     * @param mixed  $recipients
     * @param array  $parameters
     * @param array  $attachments
     * @throws Exception
     */
    protected function sendMail($template, $recipients, array $parameters = [], array $attachments = [])
    {
        $message = $this->createSwiftMessage($template, $parameters, $this->sender, $recipients);
        foreach ($attachments as $name => $content) {
            $message->attach(new Swift_Attachment($content, $name));
        }
        $this->mailer->send($message);
    }

    /**
     *
     * @param string       $templateName
     * @param array        $context
     * @param string|array $fromEmail
     * @param string       $toEmail
     * @return Swift_Message
     * @throws UnexpectedValueException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Syntax
     */
    public function createSwiftMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context = $this->twig->mergeGlobals($context);
        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        if ($fromEmail instanceof User) {
            $fromEmail = [$fromEmail->getEmail() => $fromEmail];
        }

        $recipients = $toEmail;
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }
        $compiled = [];
        foreach ($recipients as $email => $name) {
            if ($name instanceof User) {
                $compiled[$name->getEmail()] = $name;
            } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $compiled[$email] = $name;
            } elseif (filter_var($name, FILTER_VALIDATE_EMAIL)) {
                $compiled[] = $name;
            } else {
                throw new UnexpectedValueException("Unexpected values for mail: '{$email}' => '{$name}'");
            }
        }

        /** @var Swift_Message $message */
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($compiled);

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html');
            $message->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        return $message;
    }
}
