<?php

namespace Dontdrinkandroot\RestBundle\Tests\TestApp\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\TestApp\Entity\SecuredEntity;

class SecuredEntities extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $securedEntity = new SecuredEntity();
        $securedEntity->setDateTimeField(new DateTime('2015-03-04 13:12:11'));
        $securedEntity->setDateField(new DateTime('2016-01-02'));
        $securedEntity->setTimeField(new DateTime('2014-06-09 03:13:37'));

        $securedEntity->addSubResource($this->getReference('subresource-entity-2'));
        $securedEntity->addSubResource($this->getReference('subresource-entity-3'));
        $securedEntity->addSubResource($this->getReference('subresource-entity-5'));
        $securedEntity->addSubResource($this->getReference('subresource-entity-7'));
        $securedEntity->addSubResource($this->getReference('subresource-entity-11'));

        $manager->persist($securedEntity);

        $this->referenceRepository->addReference('secured-entity-0', $securedEntity);

        $securedEntity = new SecuredEntity();
        $manager->persist($securedEntity);
        $this->referenceRepository->addReference('secured-entity-1', $securedEntity);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    function getDependencies()
    {
        return [SubResourceEntities::class];
    }
}
