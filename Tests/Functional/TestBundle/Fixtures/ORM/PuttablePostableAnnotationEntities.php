<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\PuttablePostableAnnotationEntity;

class PuttablePostableAnnotationEntities extends AbstractFixture
{
    const PUTTABLE_POSTABLE_1 = 'puttable-postable-1';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $entity = new PuttablePostableAnnotationEntity();
        $entity->setPuttableByAll('puttableByAll');
        $entity->setPostableByAll('postableByAll');
        $entity->setPuttableByUser('puttableByUser');
        $entity->setPostableByUser('postableByUser');
        $entity->setPuttableByAdmin('puttableByAdmin');
        $entity->setPostableByAdmin('postableByAdmin');
        $manager->persist($entity);
        $this->addReference(self::PUTTABLE_POSTABLE_1, $entity);

        $manager->flush();
    }
}
