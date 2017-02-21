<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\MinimalEntity;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class MinimalEntities extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 50; $i++) {
            $entity = new MinimalEntity();
            $manager->persist($entity);
            $this->addReference('minimal-entity-' . $i, $entity);
        }

        $manager->flush();
    }
}
