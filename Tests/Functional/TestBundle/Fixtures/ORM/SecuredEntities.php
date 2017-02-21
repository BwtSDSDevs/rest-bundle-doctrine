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
        $manager->persist($securedEntity);

        $manager->flush();
    }
}