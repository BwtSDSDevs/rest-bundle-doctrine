<?php

namespace Dontdrinkandroot\RestBundle\Metadata\Annotation;

abstract class Method
{
    const LIST = 'LIST';
    const POST = 'POST';
    const GET = 'GET';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    /**
     * @var \Dontdrinkandroot\RestBundle\Metadata\Annotation\Right
     */
    public $right;

    abstract public function getName(): string;

    public static function create(string $name): Method
    {
        switch ($name) {
            case 'LIST':
                return new MethodList();
            case 'POST':
                return new MethodPost();
            case 'GET':
                return new MethodGet();
            case 'PUT':
                return new MethodPut();
            case 'DELETE':
                return new MethodDelete();
        }
        throw new \RuntimeException(sprintf('Unknown Method %s', $name));
    }
}
