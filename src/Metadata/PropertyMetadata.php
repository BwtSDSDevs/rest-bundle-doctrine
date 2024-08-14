<?php

namespace SdsDev\RestBundleDoctrine\Metadata;

use SdsDev\RestBundleDoctrine\Metadata\Common\CrudOperation;
use SdsDev\RestBundleDoctrine\Metadata\Attribute\Operation;
use InvalidArgumentException;
use Metadata\MergeableInterface;
use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata implements MergeableInterface
{
    private ?string $type = null;

    private ?bool $excluded = null;

    private ?bool $puttable = null;

    private ?bool $postable = null;

    private ?bool $includable = null;

    /** @var list<string>|null */
    private ?array $includablePaths = null;

    /** @var list<Operation>|null */
    private ?array $operations = null;

    private ?bool $association = null;

    private ?bool $collection = null;

    private ?string $entityClass = null;

    private ?array $attributes = null;

    public function isPuttable(): bool
    {
        return null !== $this->puttable;
    }

    public function setPuttable(bool $puttable)
    {
        $this->puttable = $puttable;
    }

    public function getPuttable(): ?bool
    {
        return $this->puttable;
    }

    public function isPostable(): bool
    {
        return null !== $this->postable;
    }

    public function setPostable(?bool $postable)
    {
        $this->postable = $postable;
    }

    public function getPostable(): ?bool
    {
        return $this->postable;
    }

    public function isIncludable(): bool
    {
        return $this->getBool($this->includable, false);
    }


    public function setIncludable(bool $includable)
    {
        $this->includable = $includable;
    }

    public function isExcluded(): bool
    {
        return $this->getBool($this->excluded, false);
    }

    public function setExcluded(bool $excluded)
    {
        $this->excluded = $excluded;
    }

    public function getIncludablePaths(): ?array
    {
        return $this->includablePaths;
    }

    public function setIncludablePaths(?array $includablePaths)
    {
        $this->includablePaths = $includablePaths;
    }

    public function isAssociation(): bool
    {
        return $this->getBool($this->association, false);
    }

    public function setAssociation(bool $association)
    {
        $this->association = $association;
    }

    public function isCollection(): bool
    {
        return $this->getBool($this->collection, false);
    }

    public function setCollection(bool $collection)
    {
        $this->collection = $collection;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type)
    {
        $this->type = $type;
    }

    public function getEntityClass(): ?bool{
        return $this->entityClass;
    }

    public function setEntityClass(?string $class)
    {
        $this->entityClass = $class;
    }

    /**
     * @param Operation[] $operations
     */
    public function setOperations(?array $operations)
    {
        $this->operations = $operations;
    }

    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    public function setAttributes(?array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function merge(MergeableInterface $other): void
    {
        if (!$other instanceof PropertyMetadata) {
            throw new InvalidArgumentException('$object must be an instance of PropertyMetadata.');
        }

        $this->type = $this->mergeField($other->type, $this->type);
        $this->puttable = $this->mergeField($other->puttable, $this->puttable);
        $this->postable = $this->mergeField($other->postable, $this->postable);
        $this->excluded = $this->mergeField($other->excluded, $this->excluded);
        $this->includable = $this->mergeField($other->includable, $this->includable);
        $this->includablePaths = $this->mergeField($other->includablePaths, $this->includablePaths);
        $this->association = $this->mergeField($other->association, $this->association);
        $this->collection = $this->mergeField($other->collection, $this->collection);
        $this->operations = $this->mergeField($other->operations, $this->operations);
        $this->entityClass = $this->mergeField($other->entityClass, $this->entityClass);
        $this->attributes = $this->mergeField($other->attributes, $this->attributes);
    }

    protected function getBool(?bool $value, bool $default): bool
    {
        if (null === $value) {
            return $default;
        }

        return $value;
    }

    private function mergeField($thisValue, $otherValue)
    {
        if (null !== $thisValue) {
            return $thisValue;
        }

        return $otherValue;
    }

    public function getOperation(CrudOperation $method): ?Operation
    {
        if (null === $this->operations) {
            return null;
        }

        foreach ($this->operations as $operation) {
            if ($method === $operation->method) {
                return $operation;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            [
                $this->class,
                $this->name,
                $this->type,
                $this->excluded,
                $this->puttable,
                $this->postable,
                $this->includable,
                $this->includablePaths,
                $this->operations,
                $this->association,
                $this->collection,
                $this->entityClass,
                $this->attributes,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->class,
            $this->name,
            $this->type,
            $this->excluded,
            $this->puttable,
            $this->postable,
            $this->includable,
            $this->includablePaths,
            $this->operations,
            $this->association,
            $this->collection,
            $this->entityClass,
            ) = unserialize($str);
    }
}
