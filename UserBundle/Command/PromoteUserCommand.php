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
 * Promote users or remove their super admin role
 *
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class PromoteUserCommand extends AbstractUserManagementCommand
{
    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('cleverage:eav-manager:promote-user')
            ->setAliases(['eavmanager:promote-user'])
            ->setDescription('Promote a user to the super admin role')
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user')
            ->addOption('demote', 'd', InputOption::VALUE_NONE, 'Disable the super admin role');
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

        $user->setSuperAdmin(!$input->getOption('demote'));
        $this->userManager->save($user);

        if ($user->isSuperAdmin()) {
            $message = $this->translator->trans(
                'user.promoted',
                ['%username%' => $username],
                'security'
            );
        } else {
            $message = $this->translator->trans(
                'user.demoted',
                ['%username%' => $username],
                'security'
            );
        }
        $output->writeln("<info>{$message}</info>");

        return 0;
    }
}
