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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Use this command to change a user password
 */
class ChangeUserPasswordCommand extends ContainerAwareCommand
{
    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('eavmanager:change-user-password')
            ->setDescription('Change the password of a user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user')
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

        $password = $input->getOption('password');
        if (null === $password && $input->isInteractive()) {
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');
            $question = new Question('<info>Password: </info>');
            $question->setHidden(true);
            $password = $questionHelper->ask($input, $output, $question);
        }

        $userManager->setPlainTextPassword($user, $password);
        $userManager->save($user);

        $output->writeln("<info>Password updated for user '{$username}'</info>");

        return 0;
    }
}
