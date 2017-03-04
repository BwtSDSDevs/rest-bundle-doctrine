<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Entity;

use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Dontdrinkandroot\RestBundle\Tests\Functional\FunctionalTestCase;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\PuttablePostableAnnotationEntities;
use Dontdrinkandroot\RestBundle\Tests\Functional\TestBundle\Fixtures\ORM\Users;
use Metadata\MetadataFactory;

class ClassMetadataTest extends FunctionalTestCase
{
    protected $environment = 'secured';

    public function testUser()
    {
        /** @var MetadataFactory $metadataFactory */
        $metadataFactory = $this->getContainer()->get('ddr_rest.metadata.factory');
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $metadataFactory->getMetadataForClass(User::class);

        $methods = $classMetadata->getMethods();
        $this->assertCount(2, $methods);

        $method = $methods['GET'];
        $this->assertEquals('GET', $method->name);
        $this->assertNull($method->right);
        $this->assertEquals(['supervisor'], $method->defaultIncludes);

        $method = $methods['POST'];
        $this->assertEquals('POST', $method->name);
        $this->assertEquals(['ROLE_ADMIN'], $method->right->attributes);
    }

    public function testSubResourceEntity()
    {
        /** @var MetadataFactory $metadataFactory */
        $metadataFactory = $this->getContainer()->get('ddr_rest.metadata.factory');
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $metadataFactory->getMetadataForClass(SubResourceEntity::class);

        //defaultIncludes: ["creator"];

        /** @var PropertyMetadata $propertyMetadata */
        $propertyMetadata = $classMetadata->propertyMetadata['creator'];
        $this->assertTrue($propertyMetadata->isIncludable());
        $this->assertTrue($propertyMetadata->isPostable());
        $this->assertTrue($propertyMetadata->getPostable()->byReference);
        $this->assertTrue($propertyMetadata->isPuttable());
        $this->assertTrue($propertyMetadata->getPuttable()->byReference);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFixtureClasses()
    {
        return [Users::class, PuttablePostableAnnotationEntities::class];
    }
}
