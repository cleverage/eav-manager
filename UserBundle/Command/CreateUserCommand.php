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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use CleverAge\EAVManager\UserBundle\Domain\Manager\UserManagerInterface;

/**
 * Use this command to create users
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * Configuration de la commande.
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('eavmanager:create-user')
            ->setDescription('Create users in the database')
            ->addArgument('username', InputArgument::REQUIRED, 'The username which is also the email')
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $userManager = $this->getContainer()->get(UserManagerInterface::class);
        if ($input->getOption('if-not-exists')) {
            try {
                $user = $userManager->loadUserByUsername($username);
                if ($user) {
                    $output->writeln("L'utilisateur existe déjà");

                    return 0;
                }
            } catch (\Exception $e) {
            }
        }

        try {
            $user = $userManager->createUser($username);
        } catch (BadUsernameException $e) {
            $output->writeln("<error>Nom d'utilisateur incorrect :\n{$e->getMessage()}</error>");

            return 1;
        }

        $user->setSuperAdmin($input->getOption('admin'));
        $password = $input->getOption('password');
        if ($password) {
            $userManager->setPlainTextPassword($user, $password);
        }
        $userManager->save($user);

        $output->writeln("<info>L'utilisateur '{$username}' a été créé avec succès</info>");

        return 0;
    }
}
