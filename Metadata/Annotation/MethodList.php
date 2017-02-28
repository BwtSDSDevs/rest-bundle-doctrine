<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class MethodList extends Method
{
    public function getName(): string
    {
        return Method::LIST;
    }
}
