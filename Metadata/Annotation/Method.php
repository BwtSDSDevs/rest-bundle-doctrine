<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 *
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class Method
{
    const LIST = 'LIST';
    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    /**
     * @Required()
     * @var string
     */
    public $name;

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $right;

    /**
     * @var array<string>
     */
    public $defaultIncludes;
}
