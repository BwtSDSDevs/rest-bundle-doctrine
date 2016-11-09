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
}
