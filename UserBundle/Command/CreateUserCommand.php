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

use CleverAge\EAVManager\UserBundle\Exception\BadUsernameException;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Use this command to create users
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class CreateUserCommand extends AbstractUserManagementCommand
{
    /**
     * Command configuration
     *
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('cleverage:eav-manager:create-user')
            ->setAliases(['eavmanager:create-user'])
            ->setDescription('Create users in the database')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username which is also the email')
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_OPTIONAL,
                'The password, if omitted the user will receive an email with a random password'
            )
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Set the user as super-admin')
            ->addOption('if-not-exists', null, InputOption::VALUE_NONE, 'Only if the user does not already exists');
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
        if ($input->getOption('if-not-exists')) {
            $user = $this->userManager->loadUserByUsername($username);
            if ($user) {
                $message = $this->translator->trans(
                    'user.already_exists',
                    [
                        '%username%' => $username,
                    ],
                    'security'
                );
                $output->writeln("<comment>{$message}</comment>");

                return 0;
            }
        }

        try {
            $user = $this->userManager->createUser($username);
        } catch (BadUsernameException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return 1;
        }

        $user->setSuperAdmin($input->getOption('admin'));
        $password = $this->getPassword($input, $output);
        if ($password) {
            $this->userManager->setPlainTextPassword($user, $password);
        }
        $this->userManager->save($user);

        $message = $this->translator->trans('user.created_success', ['%username%' => $username], 'security');
        $output->writeln("<info>{$message}</info>");

        return 0;
    }
}
