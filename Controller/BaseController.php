<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class BaseController extends FOSRestController
{

    /**
     * @param Request $request
     * @param string  $type
     *
     * @return mixed
     */
    protected function serializeRequestContent(Request $request, $type)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');
        $content = $request->getContent();
        $object = $serializer->deserialize($content, $type, $request->getContentType());

        return $object;
    }

    /**
     * @param mixed $object
     *
     * @return ConstraintViolationListInterface
     */
    protected function validate($object) {
        $validator = $this->get('validator');
        $errors = $validator->validate($object);

        return $errors;
    }

}