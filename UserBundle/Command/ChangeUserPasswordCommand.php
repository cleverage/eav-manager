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

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Use this command to change a user password
 */
class ChangeUserPasswordCommand extends AbstractUserManagementCommand
{
    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('cleverage:eav-manager:change-user-password')
            ->setAliases(['eavmanager:change-user-password'])
            ->setDescription('Change the password of a user')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'The new password of the user');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $username = $this->getUsername($input, $output);
        $user = $this->findUser($output, $username);
        if (null === $user) {
            return 1;
        }

        $password = $this->getPassword($input, $output);
        if ($password) {
            $this->userManager->setPlainTextPassword($user, $password);
        } else {
            $this->userManager->requestNewPassword($user);
        }
        $this->userManager->save($user);

        if ($password) {
            $message = $this->translator->trans(
                'user.password_changed_success',
                ['%username%' => $username],
                'security'
            );
        } else {
            $message = $this->translator->trans(
                'user.password_request_sent',
                ['%username%' => $username],
                'security'
            );
        }
        $output->writeln("<info>{$message}</info>");

        return 0;
    }
}
