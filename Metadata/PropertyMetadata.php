<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Dontdrinkandroot\RestBundle\Metadata\Annotation\Method;
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
     * @var string|null
     */
    private $subResourcePath;

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

    /**
     * @param Method[] $methods
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
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
        $this->excluded = $this->mergeField($other->excluded, $this->excluded);
        $this->includable = $this->mergeField($other->includable, $this->includable);
        $this->subResource = $this->mergeField($other->subResource, $this->subResource);
        $this->includablePaths = $this->mergeField($other->includablePaths, $this->includablePaths);
        $this->association = $this->mergeField($other->association, $this->association);
        $this->collection = $this->mergeField($other->collection, $this->collection);
        $this->subResourcePath = $this->mergeField($other->subResourcePath, $this->subResourcePath);
        $this->methods = $this->mergeField($other->methods, $this->methods);

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
}
