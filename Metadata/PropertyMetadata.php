<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata
{
    /**
     * @var string|null
     */
    private $type;

    /**
     * @var bool
     */
    private $puttable = false;

    /**
     * @var bool
     */
    private $excluded = false;

    /**
     * @var bool
     */
    private $postable = false;

    /**
     * @var bool
     */
    private $includable = false;

    /**
     * @var string[]|null
     */
    private $includablePaths;

    /**
     * @var bool
     */
    private $subResource = false;

    /**
     * @var bool
     */
    private $association = false;

    /**
     * @var bool
     */
    private $collection = false;

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
     * @var string|null
     */
    private $targetClass;

    /**
     * @return boolean
     */
    public function isPuttable()
    {
        return $this->puttable;
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
        return $this->postable;
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
        return $this->includable;
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
        return $this->subResource;
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
        return $this->excluded;
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
        return $this->association;
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
        return $this->collection;
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
}
