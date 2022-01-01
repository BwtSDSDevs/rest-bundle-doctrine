<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class Users extends Fixture implements ContainerAwareInterface
{
    const SUPERVISOR = 'user-supervisor';
    const EMPLOYEE_1 = 'employee_1';
    const EMPLOYEE_2 = 'employee_2';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserPasswordEncoderInterface $passwordEncoder */
        $passwordEncoder = $this->container->get('security.password_encoder');

        $user = new User();
        $user->setUsername('user');
        $user->setRole('ROLE_USER');
        $user->setPassword($passwordEncoder->encodePassword($user, 'user'));
        $manager->persist($user);
        $this->addReference('user-user', $user);

        $user = new User();
        $user->setUsername('admin');
        $user->setRole('ROLE_ADMIN');
        $user->setPassword($passwordEncoder->encodePassword($user, 'admin'));
        $manager->persist($user);
        $this->addReference('user-admin', $user);

        $supervisor = new User();
        $supervisor->setRole('ROLE_USER');
        $supervisor->setUsername('supervisor');
        $manager->persist($supervisor);
        $this->addReference(self::SUPERVISOR, $supervisor);

        $user = new User();
        $user->setRole('ROLE_USER');
        $user->setUsername('employee1');
        $user->setPassword($passwordEncoder->encodePassword($user, 'employee1'));
        $user->setSupervisor($supervisor);
        $manager->persist($user);
        $this->addReference(self::EMPLOYEE_1, $user);

        $user = new User();
        $user->setRole('ROLE_USER');
        $user->setUsername('employee2');
        $user->setPassword($passwordEncoder->encodePassword($user, 'employee2'));
        $manager->persist($user);
        $this->addReference(self::EMPLOYEE_2, $user);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
