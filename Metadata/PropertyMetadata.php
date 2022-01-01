<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Postable;
use Dontdrinkandroot\RestBundle\Metadata\Annotation\Puttable;
use InvalidArgumentException;
use Metadata\MergeableInterface;
use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata implements MergeableInterface
{
    /**
     * @var string|null
     */
    private $type;

    /**
     * @var bool
     */
    private $excluded;

    /**
     * @var Puttable|null
     */
    private $puttable;

    /**
     * @var Postable|null
     */
    private $postable;

    /**
     * @var bool
     */
    private $includable;

    /**
     * @var string[]|null
     */
    private $includablePaths;

    /**
     * @var bool
     */
    private $subResource;

    /**
     * @var Method[]|null
     */
    private $methods;

    /**
     * @var bool
     */
    private $association;

    /**
     * @var bool
     */
    private $collection;

    /**
     * @var bool
     */
    private $virtual;

    /**
     * @var string|null
     */
    private $subResourcePath;

    public function isPuttable(): bool
    {
        return null !== $this->puttable;
    }

    public function setPuttable(?Puttable $puttable)
    {
        $this->puttable = $puttable;
    }

    public function getPuttable(): ?Puttable
    {
        return $this->puttable;
    }

    public function isPostable(): bool
    {
        return null !== $this->postable;
    }

    public function setPostable(?Postable $postable)
    {
        $this->postable = $postable;
    }

    public function getPostable(): ?Postable
    {
        return $this->postable;
    }

    public function isIncludable(): bool
    {
        return $this->getBool($this->includable, false);
    }

    public function isVirtual(): bool
    {
        return $this->getBool($this->virtual, false);
    }

    public function setVirtual(bool $virtual)
    {
        $this->virtual = $virtual;
    }

    public function setIncludable(bool $includable)
    {
        $this->includable = $includable;
    }

    public function isSubResource(): bool
    {
        return $this->getBool($this->subResource, false);
    }

    public function setSubResource(bool $subResource)
    {
        $this->subResource = $subResource;
    }

    public function getSubResourcePath(): ?string
    {
        return $this->subResourcePath;
    }

    public function setSubResourcePath(string $subResourcePath)
    {
        $this->subResourcePath = $subResourcePath;
    }

    public function isExcluded(): bool
    {
        return $this->getBool($this->excluded, false);
    }

    public function setExcluded(bool $excluded)
    {
        $this->excluded = $excluded;
    }

    /**
     * @return null|string[]
     */
    public function getIncludablePaths(): ?array
    {
        return $this->includablePaths;
    }

    /**
     * @param null|string[] $includablePaths
     */
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

    /**
     * @param Method[] $methods
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
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
        $this->subResource = $this->mergeField($other->subResource, $this->subResource);
        $this->includablePaths = $this->mergeField($other->includablePaths, $this->includablePaths);
        $this->association = $this->mergeField($other->association, $this->association);
        $this->collection = $this->mergeField($other->collection, $this->collection);
        $this->subResourcePath = $this->mergeField($other->subResourcePath, $this->subResourcePath);
        $this->methods = $this->mergeField($other->methods, $this->methods);
        $this->virtual = $this->mergeField($other->virtual, $this->virtual);
    }

    protected function getBool(?bool $value, bool $default)
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

    public function getMethod(string $methodName): ?Method
    {
        if (null === $this->methods) {
            return null;
        }

        foreach ($this->methods as $method) {
            if ($methodName === $method->name) {
                return $method;
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
                $this->subResource,
                $this->methods,
                $this->association,
                $this->collection,
                $this->virtual,
                $this->subResourcePath,
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
            $this->subResource,
            $this->methods,
            $this->association,
            $this->collection,
            $this->virtual,
            $this->subResourcePath
            ) = unserialize($str);
    }
}
