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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Promote users or remove their super admin role
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class PromoteUserCommand extends ContainerAwareCommand
{
    /**
     * Configuration de la commande.
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('eavmanager:promote-user')
            ->setDescription('Promote a user to the super admin role')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user')
            ->addOption('downgrade', 'd', InputOption::VALUE_NONE, 'Disable the super admin role');
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
        $userManager = $this->getContainer()->get('eavmanager_user.user.manager');
        $user = null;
        try {
            $user = $userManager->loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            $output->writeln("<error>The user doesn't exists : {$username}</error>");

            return 1;
        }

        $user->setSuperAdmin(!$input->getOption('downgrade'));
        $userManager->save($user);

        if ($user->isSuperAdmin()) {
            $output->writeln("<info>The user '{$username}' is now a super-admin</info>");
        } else {
            $output->writeln("<info>The user '{$username}' is not a super-admin anymore</info>");
        }

        return 0;
    }
}
