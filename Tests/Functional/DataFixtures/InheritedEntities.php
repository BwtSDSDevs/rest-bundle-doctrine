<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity\InheritedEntity;

class InheritedEntities extends Fixture
{
    const INHERITED_ENTITY_0 = 'inherited-entity-0';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $entity = new InheritedEntity();
        $entity->setExcludedFieldOne('one');
        $entity->setExcludedFieldTwo('two');
        $entity->setSubClassField('subClass');
        $manager->persist($entity);
        $this->referenceRepository->addReference(self::INHERITED_ENTITY_0, $entity);

        $manager->flush();
    }
}
