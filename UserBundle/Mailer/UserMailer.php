<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Mailer;

use CleverAge\EAVManager\UserBundle\Configuration\Configuration;
use CleverAge\EAVManager\UserBundle\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Handles mailing to users for security steps (account creation, password reset).
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class UserMailer
{
    /** @var \Swift_Mailer */
    protected $mailer;

    /** @var \Twig_Environment */
    protected $twig;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var Configuration */
    protected $configuration;

    /**
     * @param \Swift_Mailer       $mailer
     * @param \Twig_Environment   $twig
     * @param TranslatorInterface $translator
     * @param Configuration       $configuration
     */
    public function __construct(
        \Swift_Mailer $mailer,
        \Twig_Environment $twig,
        TranslatorInterface $translator,
        Configuration $configuration
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
        $this->configuration = $configuration;
    }

    /**
     * @param User $user
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function sendNewUserMail(User $user)
    {
        $parameters = [
            'user' => $user,
            'subject' => $this->translator->trans('user.account_creation', [], 'security'),
            'company' => $this->configuration->getMailerCompany(),
        ];
        $text = $this->twig->render('CleverAgeEAVManagerUserBundle:Email:newUser.txt.twig', $parameters);
        $html = $this->twig->render('CleverAgeEAVManagerUserBundle:Email:newUser.html.twig', $parameters);

        $message = $this->createMessage();
        $message->setSubject($parameters['subject']);
        $message->setTo([$user->getEmail()]);
        $message->setBody($text);
        $message->addPart($html, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * @param User $user
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function sendResetPasswordMail(User $user)
    {
        $parameters = [
            'user' => $user,
            'subject' => $this->translator->trans('user.reset_password', [], 'security'),
            'company' => $this->configuration->getMailerCompany(),
        ];
        $text = $this->twig->render('CleverAgeEAVManagerUserBundle:Email:resetPassword.txt.twig', $parameters);
        $html = $this->twig->render('CleverAgeEAVManagerUserBundle:Email:resetPassword.html.twig', $parameters);

        $message = $this->createMessage();
        $message->setSubject($parameters['subject']);
        $message->setTo([$user->getEmail()]);
        $message->setBody($text);
        $message->addPart($html, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * @return \Swift_Message
     */
    protected function createMessage()
    {
        $message = $this->mailer->createMessage();
        $message->setFrom($this->configuration->getMailerFrom());

        return $message;
    }
}
