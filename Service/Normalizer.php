<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;

class Normalizer
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param mixed  $data
     * @param int    $depth
     * @param string $path
     *
     * @return array
     */
    public function normalize($data, int $depth = 0, string $path = '')
    {
        if (is_array($data)) {
            $normalizedData = [];
            foreach ($data as $datum) {
                $normalizedData[] = $this->normalize($datum, $depth + 1, $path);
            }

            return $normalizedData;
        }

        if (is_object($data)) {

            $normalizedData = [];

            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->metadataFactory->getMetadataForClass(get_class($data));

            /** @var PropertyMetadata $propertyMetadatum */
            foreach ($classMetadata->propertyMetadata as $propertyMetadatum) {
                if (!$propertyMetadatum->isExcluded()) {
                    $value = $propertyMetadatum->getValue($data);
                    $normalizedData[$propertyMetadatum->name] = $value;
                }
            }

            return $normalizedData;
        }

        return null;
    }
}
