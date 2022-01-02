<?php

namespace Dontdrinkandroot\RestBundle\Tests\Acceptance;

use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Dontdrinkandroot\RestBundle\Metadata\RestMetadataFactory;
use Dontdrinkandroot\RestBundle\Tests\TestApp\Entity\SubResourceEntity;
use Dontdrinkandroot\RestBundle\Tests\TestApp\Entity\User;
use Metadata\MetadataFactory;

class ClassMetadataTest extends FunctionalTestCase
{
    public function testUser()
    {
        /** @var MetadataFactory $metadataFactory */
        self::bootKernel(['environment' => 'secured']);
        $metadataFactory = static::getContainer()->get(RestMetadataFactory::class);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $metadataFactory->getMetadataForClass(User::class);

        $methods = $classMetadata->getMethods();
        $this->assertCount(2, $methods);

        $method = $methods['READ'];
        $this->assertEquals('READ', $method->name);
        $this->assertNull($method->right);
        $this->assertEquals(['supervisor'], $method->defaultIncludes);

        $method = $methods['CREATE'];
        $this->assertEquals('CREATE', $method->name);
        $this->assertEquals(['ROLE_ADMIN'], $method->right->attributes);
    }

    public function testUserSerialization()
    {
        /** @var MetadataFactory $metadataFactory */
        self::bootKernel(['environment' => 'secured'])->getContainer();
        $metadataFactory = static::getContainer()->get(RestMetadataFactory::class);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $metadataFactory->getMetadataForClass(User::class);

        $serialized = $classMetadata->serialize();

        $unserializedClassMetadata = new ClassMetadata(User::class);
        $unserializedClassMetadata->unserialize($serialized);

        $this->assertEquals($classMetadata, $unserializedClassMetadata);
    }

    public function testSubResourceEntity()
    {
        /** @var MetadataFactory $metadataFactory */
        self::bootKernel(['environment' => 'secured'])->getContainer();
        $metadataFactory = static::getContainer()->get(RestMetadataFactory::class);
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

    public function testSubResourceEntitySerialization()
    {
        /** @var MetadataFactory $metadataFactory */
        self::bootKernel(['environment' => 'secured'])->getContainer();
        $metadataFactory = static::getContainer()->get(RestMetadataFactory::class);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $metadataFactory->getMetadataForClass(SubResourceEntity::class);

        $serialized = $classMetadata->serialize();

        $unserializedClassMetadata = new ClassMetadata(SubResourceEntity::class);
        $unserializedClassMetadata->unserialize($serialized);

        $this->assertEquals($classMetadata, $unserializedClassMetadata);
    }
}
