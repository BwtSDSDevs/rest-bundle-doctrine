<?php

namespace Dontdrinkandroot\RestBundle\Serialization;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class IncludesExclusionStrategy implements ExclusionStrategyInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    public function __construct(RequestStack $requestStack, MetadataFactoryInterface $metadataFactory)
    {
        $this->requestStack = $requestStack;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipClass(ClassMetadata $metadata, Context $context)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipProperty(PropertyMetadata $property, Context $context)
    {
        $includes = $this->getIncludes();
        if ($context->getDepth() > 1) {
            return false;
        }

        $classMetadata = $this->metadataFactory->getMetadataForClass($property->class);
        /** @var \Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata[] $propertyMetadatas */
        $propertyMetadatas = $classMetadata->propertyMetadata;
        if (array_key_exists($property->name, $propertyMetadatas)) {
            $propertyMetadata = $propertyMetadatas[$property->name];
            if ($propertyMetadata->isIncludable()) {
                if (!$this->hasInclude($propertyMetadata->name)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getIncludes()
    {
        $request = $this->requestStack->getCurrentRequest();
        $includeString = $request->query->get('include');
        if (empty($includeString)) {
            return [];
        }

        return explode(',', $includeString);
    }

    private function hasInclude($name)
    {
        $includes = $this->getIncludes();

        return in_array($name, $includes);
    }
}
