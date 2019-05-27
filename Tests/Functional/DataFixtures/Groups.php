<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\Group;

class Groups extends Fixture implements DependentFixtureInterface
{
    const EMPLOYEES = 'employees';
    const GROUP_2 = 'group-2';
    const GROUP_3 = 'group-3';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $group = new Group();
        $group->setName('employees');
        $group->getUsers()->add($this->getReference(Users::EMPLOYEE_1));
        $manager->persist($group);
        $this->addReference(self::EMPLOYEES, $group);

        $group = new Group();
        $group->setName('group2');
        $manager->persist($group);
        $this->addReference(self::GROUP_2, $group);

        $group = new Group();
        $group->setName('group3');
        $manager->persist($group);
        $this->addReference(self::GROUP_3, $group);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    function getDependencies()
    {
        return [Users::class];
    }
}
