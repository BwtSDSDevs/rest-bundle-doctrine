<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Metadata\MergeableInterface;
use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata implements MergeableInterface
{
    public function __construct($class, $name)
    {
        try {
            parent::__construct($class, $name);
        } catch (\ReflectionException $e) {
            /* Ignore missing property definition as they might just be overridden and therefore only exist in the
              parent class. They will be accessible after merging. */
        }
    }

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var bool
     */
    private $puttable;

    /**
     * @var bool
     */
    private $excluded;

    /**
     * @var bool
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
     * @var bool
     */
    private $association;

    /**
     * @var bool
     */
    private $collection;

    /**
     * @var string|null
     */
    private $subResourcePath;

    /**
     * @var Right|null
     */
    private $subResourceListRight;

    /**
     * @var Right|null
     */
    private $subResourcePostRight;

    /**
     * @var Right|null
     */
    private $subResourcePutRight;

    /**
     * @var Right|null
     */
    private $subResourceDeleteRight;

    /**
     * @var string|null
     */
    private $targetClass;

    /**
     * @return boolean
     */
    public function isPuttable()
    {
        return $this->getBool($this->puttable, false);
    }

    /**
     * @param boolean $puttable
     */
    public function setPuttable($puttable)
    {
        $this->puttable = $puttable;
    }

    /**
     * @return boolean
     */
    public function isPostable()
    {
        return $this->getBool($this->postable, false);
    }

    /**
     * @param boolean $postable
     */
    public function setPostable($postable)
    {
        $this->postable = $postable;
    }

    /**
     * @return boolean
     */
    public function isIncludable()
    {
        return $this->getBool($this->includable, false);
    }

    /**
     * @param boolean $includable
     */
    public function setIncludable($includable)
    {
        $this->includable = $includable;
    }

    /**
     * @return boolean
     */
    public function isSubResource()
    {
        return $this->getBool($this->subResource, false);
    }

    /**
     * @param boolean $subResource
     */
    public function setSubResource($subResource)
    {
        $this->subResource = $subResource;
    }

    /**
     * @return Right|null
     */
    public function getSubResourceListRight()
    {
        return $this->subResourceListRight;
    }

    /**
     * @param Right|null $subResourceListRight
     */
    public function setSubResourceListRight($subResourceListRight)
    {
        $this->subResourceListRight = $subResourceListRight;
    }

    /**
     * @return Right|null
     */
    public function getSubResourcePostRight()
    {
        return $this->subResourcePostRight;
    }

    /**
     * @param Right|null $subResourcePostRight
     */
    public function setSubResourcePostRight($subResourcePostRight)
    {
        $this->subResourcePostRight = $subResourcePostRight;
    }

    /**
     * @param Right|null $subResourcePutRight
     */
    public function setSubResourcePutRight($subResourcePutRight)
    {
        $this->subResourcePutRight = $subResourcePutRight;
    }

    /**
     * @return Right|null
     */
    public function getSubResourcePutRight()
    {
        return $this->subResourcePutRight;
    }

    /**
     * @param Right|null $subResourceDeleteRight
     */
    public function setSubResourceDeleteRight($subResourceDeleteRight)
    {
        $this->subResourceDeleteRight = $subResourceDeleteRight;
    }

    /**
     * @return Right|null
     */
    public function getSubResourceDeleteRight()
    {
        return $this->subResourceDeleteRight;
    }

    /**
     * @return null|string
     */
    public function getTargetClass()
    {
        return $this->targetClass;
    }

    /**
     * @param null|string $targetClass
     */
    public function setTargetClass($targetClass)
    {
        $this->targetClass = $targetClass;
    }

    /**
     * @return null|string
     */
    public function getSubResourcePath()
    {
        return $this->subResourcePath;
    }

    /**
     * @param null|string $subResourcePath
     */
    public function setSubResourcePath($subResourcePath)
    {
        $this->subResourcePath = $subResourcePath;
    }

    /**
     * @return bool
     */
    public function isExcluded(): bool
    {
        return $this->getBool($this->excluded, false);
    }

    /**
     * @param bool $excluded
     */
    public function setExcluded(bool $excluded)
    {
        $this->excluded = $excluded;
    }

    /**
     * @return null|\string[]
     */
    public function getIncludablePaths(): ?array
    {
        return $this->includablePaths;
    }

    /**
     * @param null|\string[] $includablePaths
     */
    public function setIncludablePaths(?array $includablePaths)
    {
        $this->includablePaths = $includablePaths;
    }

    /**
     * @return bool
     */
    public function isAssociation(): bool
    {
        return $this->getBool($this->association, false);
    }

    /**
     * @param bool $association
     */
    public function setAssociation(bool $association)
    {
        $this->association = $association;
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->getBool($this->collection, false);
    }

    /**
     * @param bool $collection
     */
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

    public function merge(MergeableInterface $other)
    {
        if (!$other instanceof PropertyMetadata) {
            throw new \InvalidArgumentException('$object must be an instance of PropertyMetadata.');
        }

        $this->reflection = $this->mergeField($other->reflection, $this->reflection);
        $this->type = $this->mergeField($other->type, $this->type);
        $this->puttable = $this->mergeField($other->puttable, $this->puttable);
        $this->postable = $this->mergeField($other->postable, $this->postable);
        $this->type = $this->mergeField($other->type, $other->type);
        $this->excluded = $this->mergeField($other->excluded, $this->excluded);
        $this->includable = $this->mergeField($other->includable, $this->includable);
        $this->subResource = $this->mergeField($other->subResource, $this->subResource);
        $this->includablePaths = $this->mergeField($other->includablePaths, $this->includablePaths);
        $this->association = $this->mergeField($other->association, $this->association);
        $this->collection = $this->mergeField($other->collection, $this->collection);
        $this->subResourcePath = $this->mergeField($other->subResourcePath, $this->subResourcePath);
        $this->subResourceListRight = $this->mergeField($other->subResourceListRight, $this->subResourceListRight);
        $this->subResourcePostRight = $this->mergeField($other->subResourcePostRight, $this->subResourcePostRight);
        $this->subResourcePutRight = $this->mergeField($other->subResourcePutRight, $this->subResourcePutRight);
        $this->subResourceDeleteRight = $this->mergeField(
            $other->subResourceDeleteRight,
            $this->subResourceDeleteRight
        );
        $this->targetClass = $this->mergeField($other->targetClass, $this->targetClass);

        return $this;
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
}
