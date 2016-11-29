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
        $classMetadata = $this->metadataFactory->getMetadataForClass($property->class);
        /** @var \Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata[] $propertyMetadatas */
        $propertyMetadatas = $classMetadata->propertyMetadata;
        if (array_key_exists($property->name, $propertyMetadatas)) {
            $propertyMetadata = $propertyMetadatas[$property->name];
            if ($propertyMetadata->isIncludable()) {
                $path = $this->getPathString($context, $property);
                if (!$this->hasInclude($path, $includes)) {
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

    private function hasInclude($path, array $includes)
    {
        return in_array($path, $includes);
    }

    private function getPathString(Context $context, PropertyMetadata $property)
    {
        $name = $property->name;
        if (1 === $context->getDepth()) {
            return $name;
        }
        $pathString = implode('.', $context->getCurrentPath());
        $pathString .= '.' . $name;

        return $pathString;
    }
}
