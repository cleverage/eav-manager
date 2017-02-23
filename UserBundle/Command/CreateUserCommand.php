<?php

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
 * Commande servant à créer des utilisateurs
 */
class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * Configuration de la commande
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('eavmanager:create-user')
            ->setDescription('Crée ou modifie un utilisateur et le persiste en BDD')
            ->addArgument('username', InputArgument::REQUIRED, "Le username de l'utilisateur")
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Set le mot de passe immédiatement')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Active le role admin')
            ->addOption('if-not-exists', null, InputOption::VALUE_NONE, "Seulement si l'utilisateur n'existe pas déjà");
    }

    /**
     * @param InputInterface  $input
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
     * @return int|null|void
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
