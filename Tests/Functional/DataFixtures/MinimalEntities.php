<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\MinimalEntity;

class MinimalEntities extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 49; $i++) {
            $entity = new MinimalEntity();
            $entity->setIntegerValue($i);
            $manager->persist($entity);
            $this->addReference('minimal-entity-' . $i, $entity);
        }

        $manager->flush();
    }
}
