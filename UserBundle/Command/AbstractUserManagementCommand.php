<?php
/*
 * This file is part of the CleverAge/EAVManager package.
 *
 * Copyright (c) 2015-2018 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\EAVManager\UserBundle\Command;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use CleverAge\EAVManager\UserBundle\Domain\Manager\UserManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Common logic for user management
 */
abstract class AbstractUserManagementCommand extends Command
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var UserManagerInterface */
    protected $userManager;

    /**
     * @param TranslatorInterface  $translator
     * @param UserManagerInterface $userManager
     */
    public function __construct(TranslatorInterface $translator, UserManagerInterface $userManager)
    {
        parent::__construct();
        $this->translator = $translator;
        $this->userManager = $userManager;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function getUsername(InputInterface $input, OutputInterface $output): string
    {
        $username = $input->getArgument('username');
        if (null === $username) {
            if (!$input->isInteractive()) {
                throw new \UnexpectedValueException('Missing username argument');
            }
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');
            $question = new Question(
                "<info>{$this->translator->trans('admin.user.form.username.label')}: </info>"
            );
            $username = $questionHelper->ask($input, $output, $question);
        }

        return $username;
    }

    /**
     * @param OutputInterface $output
     * @param string          $username
     *
     * @return null|UserInterface|User
     */
    protected function findUser(OutputInterface $output, string $username): ?User
    {
        try {
            /** @var User $user */

            return $this->userManager->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $message = $this->translator->trans(
                'user.does_not_exists',
                ['%username%' => $username],
                'security'
            );
            $output->writeln("<error>{$message}</error>");

            return null;
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return null|string
     */
    protected function getPassword(InputInterface $input, OutputInterface $output): ?string
    {
        $password = $input->getOption('password');
        if (null === $password && $input->isInteractive()) {
            $message = $this->translator->trans('user.blank_password_info', [], 'security');
            $output->writeln("<info>{$message}</info>");
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');
            $question = new Question(
                "<info>{$this->translator->trans('admin.user.form.password.label')}: </info>"
            );
            $question->setHidden(true);
            $password = (string) $questionHelper->ask($input, $output, $question);
        }

        return $password;
    }
}
