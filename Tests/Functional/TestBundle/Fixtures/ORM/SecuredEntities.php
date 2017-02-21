<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\SecuredEntity;

class SecuredEntities extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $securedEntity = new SecuredEntity();
        $securedEntity->setDateTimeField(new \DateTime('2015-03-04 13:12:11'));
        $securedEntity->setDateField(new \DateTime('2016-01-02'));
        $securedEntity->setTimeField(new \DateTime('2014-06-09 03:13:37'));
        $manager->persist($securedEntity);
        $this->referenceRepository->addReference('secured-entity-0', $securedEntity);

        $manager->flush();
    }
}
