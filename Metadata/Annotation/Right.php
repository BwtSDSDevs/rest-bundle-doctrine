<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Right
{
    /**
     * @Required
     * @var array<string>
     */
    public $attributes;

    /**
     * @var string
     */
    public $path;
}
