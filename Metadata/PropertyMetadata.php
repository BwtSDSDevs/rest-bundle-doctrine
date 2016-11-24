<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

use Dontdrinkandroot\RestBundle\Metadata\Annotation\Right;
use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata
{
    /**
     * @var bool
     */
    private $puttable = false;

    /**
     * @var bool
     */
    private $postable = false;

    /**
     * @var bool
     */
    private $includable = false;

    /**
     * @var bool
     */
    private $subResource = false;

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
    private $subResourceEntityClass;

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
    public function getSubResourceEntityClass()
    {
        return $this->subResourceEntityClass;
    }

    /**
     * @param null|string $subResourceEntityClass
     */
    public function setSubResourceEntityClass($subResourceEntityClass)
    {
        $this->subResourceEntityClass = $subResourceEntityClass;
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
}
