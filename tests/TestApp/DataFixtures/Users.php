<?php

namespace Niebvelungen\RestBundleDoctrine\Tests\TestApp\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Niebvelungen\RestBundleDoctrine\Tests\TestApp\Entity\User;
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

        $user = new User(
            'user',
            'ROLE_USER'
        );
        $user->setPassword($passwordEncoder->encodePassword($user, 'user'));
        $manager->persist($user);
        $this->addReference('user-user', $user);

        $user = new User(
            'admin',
            'ROLE_ADMIN'
        );
        $user->setPassword($passwordEncoder->encodePassword($user, 'admin'));
        $manager->persist($user);
        $this->addReference('user-admin', $user);

        $supervisor = new User(
            'supervisor',
            'ROLE_SUPERVISOR'
        );
        $manager->persist($supervisor);
        $this->addReference(self::SUPERVISOR, $supervisor);

        $user = new User(
            'employee',
            'ROLE_USER'
        );
        $user->setPassword($passwordEncoder->encodePassword($user, 'employee1'));
        $user->setSupervisor($supervisor);
        $manager->persist($user);
        $this->addReference(self::EMPLOYEE_1, $user);

        $user = new User(
            'employee',
            'ROLE_USER'
        );
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
