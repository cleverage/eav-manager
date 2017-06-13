<?php
/*
 *    CleverAge/EAVManager
 *    Copyright (C) 2015-2017 Clever-Age
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
            'subject' => $this->translator->trans('eavmanager.user.security.account_creation'),
            'company' => $this->configuration->getMailerCompany(),
        ];
        $text = $this->twig->render('CleverAgeEAVManagerUserBundle:Email:newUser.txt.twig', $parameters);
        $html = $this->twig->render('CleverAgeEAVManagerUserBundle:Email:newUser.html.twig', $parameters);

        $message = $this->createMessage();
        $message->setSubject($parameters['subject']);
        $message->setTo([$user->getUsername()]);
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
            'subject' => $this->translator->trans('eavmanager.user.security.reset_password'),
            'company' => $this->configuration->getMailerCompany(),
        ];
        $text = $this->twig->render('CleverAgeEAVManagerUserBundle:Email:resetPassword.txt.twig', $parameters);
        $html = $this->twig->render('CleverAgeEAVManagerUserBundle:Email:resetPassword.html.twig', $parameters);

        $message = $this->createMessage();
        $message->setSubject($parameters['subject']);
        $message->setTo([$user->getUsername()]);
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
