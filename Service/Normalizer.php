<?php

namespace Dontdrinkandroot\RestBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
use Dontdrinkandroot\RestBundle\Metadata\ClassMetadata;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Normalizer
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    function __construct(
        MetadataFactoryInterface $metadataFactory,
        PropertyAccessorInterface $propertyAccessor,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->urlGenerator = $urlGenerator;
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

            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getClass($data));

            $normalizedData = [];

            if ($classMetadata->isRestResource() && $classMetadata->hasMethod(Method::GET) && $this->isIncluded(
                    $path,
                    ['_links'],
                    $includes
                )
            ) {
                $selfLink = $this->urlGenerator->generate(
                    $classMetadata->namePrefix . '.get',
                    ['id' => $this->propertyAccessor->getValue($data, $classMetadata->getIdField())],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $normalizedData['_links'] = [
                    'self' => [
                        'href' => $selfLink
                    ]
                ];
            }

            /** @var PropertyMetadata $propertyMetadatum */
            foreach ($classMetadata->propertyMetadata as $propertyMetadatum) {

                if ($propertyMetadatum->isExcluded()) {
                    continue;
                }

                if ($propertyMetadatum->isAssociation()) {

                    /* Inlude if includable AND it is on include path */
                    if ($propertyMetadatum->isIncludable() && $this->isIncluded(
                            $path,
                            $propertyMetadatum->getIncludablePaths(),
                            $includes
                        )
                    ) {
                        $value = $this->propertyAccessor->getValue($data, $propertyMetadatum->name);
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
                            $path,
                            $propertyMetadatum->getIncludablePaths(),
                            $includes
                        )
                    ) {
                        $value = $this->propertyAccessor->getValue($data, $propertyMetadatum->name);
                        if (is_scalar($value) || array_key_exists($propertyMetadatum->getType(), Type::getTypesMap())) {
                            $normalizedData[$propertyMetadatum->name] = $this->normalizeField(
                                $value,
                                $propertyMetadatum
                            );
                        } else {
                            $normalizedData[$propertyMetadatum->name] = $this->normalize(
                                $value,
                                $includes,
                                $depth + 1,
                                $this->appendPath($path, $propertyMetadatum->name)
                            );
                        }
                    }
                }
            }

            return $normalizedData;
        }

        return null;
    }

    private function isIncluded($currentPath, array $paths, ?array $includes): bool
    {
        if (null === $includes) {
            return false;
        }

        foreach ($paths as $path) {
            if (in_array($this->appendPath($currentPath, $path), $includes)) {
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

    private function normalizeField($value, PropertyMetadata $propertyMetadata)
    {
        switch ($propertyMetadata->getType()) {
            case 'datetime':
                if (null === $value) {
                    return null;
                }

                /** @var $value \DateTime */
                return $value->format('Y-m-d H:i:s');

            case 'date':
                if (null === $value) {
                    return null;
                }

                /** @var $value \DateTime */
                return $value->format('Y-m-d');

            case 'time':
                if (null === $value) {
                    return null;
                }

                /** @var $value \DateTime */
                return $value->format('H:i:s');

            default:
                return $value;
        }
    }
}
