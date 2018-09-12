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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use CleverAge\EAVManager\UserBundle\Domain\Manager\UserManagerInterface;

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
        $userManager = $this->getContainer()->get(UserManagerInterface::class);
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
