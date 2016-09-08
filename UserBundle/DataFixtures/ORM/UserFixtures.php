<?php

namespace CleverAge\EAVManager\UserBundle\DataFixtures\ORM;

use CleverAge\EAVManager\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends AbstractFixture implements FixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('admin@eavmanager.com')
            ->setEnabled(true)
            ->setPlainPassword('admin')
            ->setSuperAdmin(true)
            ->setUsername('admin');

        $manager->persist($user);
        $manager->flush();
    }
}
