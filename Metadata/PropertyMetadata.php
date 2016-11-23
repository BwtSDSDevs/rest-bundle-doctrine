<?php

namespace Dontdrinkandroot\RestBundle\Metadata;

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
}
