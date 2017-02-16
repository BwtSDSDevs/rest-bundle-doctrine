<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\Common\Collections\Collection;
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
     * @param mixed    $data
     * @param string[] $includes
     * @param int      $depth
     * @param string   $path
     *
     * @return array
     */
    public function normalize($data, $includes = [], int $depth = 0, string $path = '')
    {
        if (is_array($data)) {
            $normalizedData = [];
            foreach ($data as $datum) {
                $normalizedData[] = $this->normalize($datum, $includes, $depth + 1, $path);
            }

            return $normalizedData;
        }

        if (is_object($data)) {

            $normalizedData = [];

            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->metadataFactory->getMetadataForClass(get_class($data));

            /** @var PropertyMetadata $propertyMetadatum */
            foreach ($classMetadata->propertyMetadata as $propertyMetadatum) {

                if ($propertyMetadatum->isExcluded()) {
                    continue;
                }

                if ($propertyMetadatum->isAssociation()) {

                    /* Inlude if includable AND it is on include path */
                    if ($propertyMetadatum->isIncludable() && $this->isIncluded(
                            $propertyMetadatum->getIncludablePaths(),
                            $includes
                        )
                    ) {
                        $value = $propertyMetadatum->getValue($data);
                        if ($propertyMetadatum->isCollection()) {
                            /** @var Collection $value */
                            $value = $value->getValues();
                        }
                        $normalizedData[$propertyMetadatum->name] = $this->normalize(
                            $value,
                            $includes,
                            $depth + 1,
                            $this->appendPath($path, $propertyMetadatum->name)
                        );
                    }
                } else {

                    /* Inlude if includable is missing OR it is on include path */
                    if (!$propertyMetadatum->isIncludable() || $this->isIncluded(
                            $propertyMetadatum->getIncludablePaths(),
                            $includes
                        )
                    ) {
                        $value = $propertyMetadatum->getValue($data);
                        $normalizedData[$propertyMetadatum->name] = $value;
                    }
                }
            }

            return $normalizedData;
        }

        return null;
    }

    private function isIncluded(array $paths, ?array $includes): bool
    {
        if (null === $includes) {
            return false;
        }

        foreach ($paths as $path) {
            if (in_array($path, $includes)) {
                return true;
            }
        }

        return false;
    }

    private function appendPath($path, $name)
    {
        if (null === $path || '' === $path) {
            return $name;
        }

        return $path . '.' . $name;
    }
}
