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
}
