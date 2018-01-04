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

namespace CleverAge\EAVManager\UserBundle\Command;

use CleverAge\EAVManager\UserBundle\Exception\BadUsernameException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws InvalidArgumentException
     * @throws \LogicException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \InvalidArgumentException
     * @throws OptimisticLockException
     * @throws ORMInvalidArgumentException
     * @throws UsernameNotFoundException
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $userManager = $this->getContainer()->get('eavmanager_user.user.manager');
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
