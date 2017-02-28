<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class MethodPost extends Method
{
    public function getName(): string
    {
        return Method::POST;
    }
}
