<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\SubResourceEntity;

class SubResourceEntities extends Fixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 33; $i++) {
            $entity = new SubResourceEntity();
            $manager->persist($entity);
            $this->addReference('subresource-entity-' . $i, $entity);
        }

        $manager->flush();
    }
}
