<?php

namespace Dontdrinkandroot\RestBundle\Tests\Acceptance;

use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Dontdrinkandroot\RestBundle\Metadata\RestMetadataFactory;
use Dontdrinkandroot\RestBundle\Tests\TestApp\Entity\SubResourceEntity;
use Dontdrinkandroot\RestBundle\Tests\TestApp\Entity\User;
use Metadata\MetadataFactory;

class ClassMetadataTest extends FunctionalTestCase
{
    public function testUser(): void
    {
        /** @var MetadataFactory $metadataFactory */
        self::bootKernel(['environment' => 'secured']);
        $metadataFactory = static::getContainer()->get(RestMetadataFactory::class);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $metadataFactory->getMetadataForClass(User::class);

        $methods = $classMetadata->getOperations();
        $this->assertCount(2, $methods);

        $method = $methods['READ'];
        $this->assertEquals(CrudOperation::READ, $method->method);
        $this->assertNull($method->granted);
        $this->assertNull($method->grantedExpression);
        $this->assertEquals(['supervisor'], $method->defaultIncludes);

        $method = $methods['CREATE'];
        $this->assertEquals(CrudOperation::CREATE, $method->method);
        $this->assertEquals('ROLE_ADMIN', $method->granted);
    }

    public function testUserSerialization(): void
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

    public function testSubResourceEntity(): void
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

    public function testSubResourceEntitySerialization(): void
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
