<?php

namespace Niebvelungen\RestBundleDoctrine\Serializer;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

use Niebvelungen\RestBundleDoctrine\Defaults\Defaults;
use Niebvelungen\RestBundleDoctrine\Metadata\ClassMetadata;
use Niebvelungen\RestBundleDoctrine\Metadata\Common\Asserted;
use Niebvelungen\RestBundleDoctrine\Metadata\Common\CrudOperation;
use Niebvelungen\RestBundleDoctrine\Metadata\PropertyMetadata;
use Niebvelungen\RestBundleDoctrine\Metadata\RestMetadataFactory;
use LogicException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RestNormalizer implements NormalizerInterface
{
    const DDR_REST_INCLUDES = 'ddrRestIncludes';
    const DDR_REST_PATH = 'ddrRestPath';
    const DDR_REST_DEPTH = 'ddrRestDepth';

    public function __construct(
        private readonly RestMetadataFactory $metadataFactory,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {

        if (!array_key_exists(self::DDR_REST_INCLUDES, $context)) {
            throw new LogicException('Includes missing');
        }

        if (!array_key_exists(self::DDR_REST_PATH, $context)) {
            throw new LogicException('Path missing');
        }

        if (!array_key_exists(self::DDR_REST_DEPTH, $context)) {
            throw new LogicException('Depth missing');
        }

        $includes = $context[self::DDR_REST_INCLUDES];
        $path = $context[self::DDR_REST_PATH];
        $depth = $context[self::DDR_REST_DEPTH];

        if (is_array($object)) {
            $normalizedData = [];
            foreach ($object as $datum) {
                $normalizedData[] = $this->normalize(
                    $datum,
                    $format,
                    [
                        self::DDR_REST_INCLUDES => $includes,
                        self::DDR_REST_DEPTH => $depth + 1,
                        self::DDR_REST_PATH => $path
                    ]
                );
            }

            return $normalizedData;
        }

        if (is_object($object)) {
            /** @var ClassMetadata $classMetadata */
            $classMetadata = $this->metadataFactory->getMetadataForClass(get_class($object));

            $normalizedData = [];

            if (
                $classMetadata->isRestResource()
                && $classMetadata->hasOperation(CrudOperation::READ)
                && $this->isIncluded($path, ['_links'], $includes)
            ) {
                $selfLink = $this->urlGenerator->generate(
                    $classMetadata->namePrefix . '.get',
                    ['id' => $this->propertyAccessor->getValue($object, $classMetadata->getIdField())],
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
                    /* Include if includable AND it is on include path */
                    if (
                        $propertyMetadatum->isIncludable()
                        && $this->isIncluded($path, $propertyMetadatum->getIncludablePaths(), $includes)
                    ) {
                        $value = $this->propertyAccessor->getValue($object, $propertyMetadatum->name);
                        if ($propertyMetadatum->isCollection()) {
                            /** @var Collection $value */
                            $value = $value->getValues();
                        }
                        $normalizedData[$propertyMetadatum->name] = $this->normalize(
                            $value,
                            $format,
                            [
                                self::DDR_REST_INCLUDES => $includes,
                                self::DDR_REST_DEPTH => $depth + 1,
                                self::DDR_REST_PATH => $this->appendPath($path, $propertyMetadatum->name)
                            ]
                        );
                    }
                } else {
                    /* Include if includable is missing OR it is on include path */
                    if (
                        !$propertyMetadatum->isIncludable()
                        || $this->isIncluded($path, $propertyMetadatum->getIncludablePaths(), $includes)
                    ) {
                        $value = $this->propertyAccessor->getValue($object, $propertyMetadatum->name);
                        if (is_scalar($value) || array_key_exists($propertyMetadatum->getType(), Type::getTypesMap())) {
                            $normalizedData[$propertyMetadatum->name] = $this->normalizeField(
                                $value,
                                $propertyMetadatum
                            );
                        } else {
                            $normalizedData[$propertyMetadatum->name] = $this->normalize(
                                $value,
                                $format,
                                [
                                    self::DDR_REST_INCLUDES => $includes,
                                    self::DDR_REST_DEPTH => $depth + 1,
                                    self::DDR_REST_PATH => $this->appendPath($path, $propertyMetadatum->name)
                                ]
                            );
                        }
                    }
                }
            }

            return $normalizedData;
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     * @param mixed $data
     * @param null $format
     * @param array $context
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return Defaults::SERIALIZE_FORMAT === $format;
    }

    private function isIncluded($currentPath, array $paths, ?array $includes): bool
    {
        if (null === $includes) {
            return false;
        }

        foreach ($paths as $path) {
            if (in_array($this->appendPath($currentPath, $path), $includes, true)) {
                return true;
            }
        }

        return false;
    }

    private function appendPath($path, $name): string
    {
        if (null === $path || '' === $path) {
            return $name;
        }

        return $path . '.' . $name;
    }

    private function normalizeField($value, PropertyMetadata $propertyMetadata)
    {
        switch ($propertyMetadata->getType()) {
            case Types::DATETIME_MUTABLE:
                if (null === $value) {
                    return null;
                }

                return Asserted::instanceOf($value, DateTimeInterface::class)->format('Y-m-d H:i:s');

            case Types::DATE_MUTABLE:
                if (null === $value) {
                    return null;
                }

                return Asserted::instanceOf($value, DateTimeInterface::class)->format('Y-m-d');

            case Types::TIME_MUTABLE:
                if (null === $value) {
                    return null;
                }

                return Asserted::instanceOf($value, DateTimeInterface::class)->format('H:i:s');

            default:
                return $value;
        }
    }

    public function getSupportedTypes(?string $format): array
    {
        if(Defaults::SERIALIZE_FORMAT === $format)
            return [ '*' => true];

        return [];
    }
}
