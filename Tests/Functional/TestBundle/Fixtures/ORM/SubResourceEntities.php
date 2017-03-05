<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\SubResourceEntity;

class SubResourceEntities extends AbstractFixture
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
