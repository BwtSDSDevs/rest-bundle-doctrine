<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\AccessToken;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\User;

class Users extends AbstractFixture
{
    const SUPERVISOR = 'user-supervisor';
    const EMPLOYEE_1 = 'employee_1';
    const EMPLOYEE_2 = 'employee_2';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('user');
        $user->setRole('ROLE_USER');
        $manager->persist($user);
        $this->addReference('user-user', $user);

        $accessToken = new AccessToken();
        $accessToken->setToken('user-user');
        $accessToken->setUser($user);
        $manager->persist($accessToken);
        $this->addReference('token-user-user', $accessToken);

        $user = new User();
        $user->setUsername('admin');
        $user->setRole('ROLE_ADMIN');
        $manager->persist($user);
        $this->addReference('user-admin', $user);

        $accessToken = new AccessToken();
        $accessToken->setToken('user-admin');
        $accessToken->setUser($user);
        $manager->persist($accessToken);
        $this->addReference('token-user-admin', $accessToken);

        $supervisor = new User();
        $supervisor->setRole('ROLE_USER');
        $supervisor->setUsername('supervisor');
        $manager->persist($supervisor);
        $this->addReference(self::SUPERVISOR, $supervisor);

        $user = new User();
        $user->setRole('ROLE_USER');
        $user->setUsername('employee1');
        $user->setSupervisor($supervisor);
        $manager->persist($user);
        $this->addReference(self::EMPLOYEE_1, $user);

        $user = new User();
        $user->setRole('ROLE_USER');
        $user->setUsername('employee2');
        $manager->persist($user);
        $this->addReference(self::EMPLOYEE_2, $user);

        $manager->flush();
    }
}
