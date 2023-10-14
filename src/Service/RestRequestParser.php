<?php

namespace Dontdrinkandroot\RestBundle\Service;

use DateTime;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\RestBundle\Metadata\Attribute\Right;
use Dontdrinkandroot\RestBundle\Metadata\PropertyMetadata;
use Metadata\MetadataFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class RestRequestParser implements RestRequestParserInterface
{
    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var AuthorizationCheckerInterface|null
     */
    private $authorizationChecker;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        MetadataFactory $metadataFactory,
        PropertyAccessor $propertyAccessor,
        EntityManagerInterface $entityManager
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->propertyAccessor = $propertyAccessor;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function parseEntity(Request $request, $entityClass, $entity = null)
    {
        $method = $request->getMethod();

        $data = $this->getRequestContent($request);

        if (null === $entity) {
            $entity = new $entityClass;
        }

        $this->updateObject($entity, $method, $data);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestContent(Request $request)
    {
        $format = $request->getRequestFormat();

        if ('json' !== $format) {
            throw new RuntimeException(sprintf('Unsupported format "%s"', $format));
        }

        $content = $request->getContent();
        if ('' !== $content) {
            return json_decode($content, true);
        }

        return $request->request->all();
    }

    /**
     * @param object $object Access by reference.
     * @param string $method
     * @param array  $data
     */
    protected function updateObject(&$object, $method, $data)
    {
        $classMetadata = $this->metadataFactory->getMetadataForClass(ClassUtils::getClass($object));

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $classMetadata->propertyMetadata)) {
                /** @var PropertyMetadata $propertyMetadata */
                $propertyMetadata = $classMetadata->propertyMetadata[$key];
                if ($this->isUpdateable($object, $method, $propertyMetadata)) {
                    $this->updateProperty($object, $method, $propertyMetadata, $value);
                }
            }
        }
    }

    /**
     * @param object           $object Access by reference.
     * @param string           $method
     * @param PropertyMetadata $propertyMetadata
     * @param mixed            $value
     */
    protected function updateProperty(&$object, string $method, PropertyMetadata $propertyMetadata, $value)
    {
        $byReference = $this->isUpdateableByReference($propertyMetadata, $method);
        if ($byReference) {
            $this->updateByReference($object, $propertyMetadata, $value);
        } elseif (array_key_exists($propertyMetadata->getType(), Type::getTypesMap())) {
            $convertedValue = $this->convert($propertyMetadata->getType(), $value);
            $this->propertyAccessor->setValue($object, $propertyMetadata->name, $convertedValue);
        } else {
            $this->updatePropertyObject($object, $method, $propertyMetadata, $value);
        }
    }

    private function updateByReference(&$object, PropertyMetadata $propertyMetadata, $value)
    {
        if (null === $value) {
            $this->propertyAccessor->setValue($object, $propertyMetadata->name, null);
        } else {
            $type = $propertyMetadata->getType();
            $classMetadata = $this->entityManager->getClassMetadata($type);
            $identifiers = $classMetadata->getIdentifier();
            $id = [];
            foreach ($identifiers as $idName) {
                $id[$idName] = $value[$idName];
            }
            $reference = $this->entityManager->getReference($type, $id);
            $this->propertyAccessor->setValue($object, $propertyMetadata->name, $reference);
        }
    }

    protected function updatePropertyObject(&$object, string $method, PropertyMetadata $propertyMetadata, $value)
    {
        $propertyObject = $this->propertyAccessor->getValue($object, $propertyMetadata->name);
        if (null === $propertyObject) {
            $type = $propertyMetadata->getType();
            $propertyObject = new $type;
        }

        $this->updateObject($propertyObject, $method, $value);
        $this->propertyAccessor->setValue($object, $propertyMetadata->name, $propertyObject);
    }

    /**
     * @param string           $method
     * @param object           $object
     * @param PropertyMetadata $propertyMetadata
     *
     * @return bool
     */
    protected function isUpdateable($object, string $method, PropertyMetadata $propertyMetadata): bool
    {
        if ((Request::METHOD_PUT === $method || Request::METHOD_PATCH === $method) && $propertyMetadata->isPuttable()) {
            return $this->isGranted($object, $propertyMetadata->getPuttable()->right);
        }

        if (Request:: METHOD_POST === $method && $propertyMetadata->isPostable()) {
            return $this->isGranted($object, $propertyMetadata->getPostable()->right);
        }

        return false;
    }

    private function isGranted($object, ?Right $right): bool
    {
        if (null === $right) {
            return true;
        }

        /* If no Security is enabled always deny access */
        if (null === $this->authorizationChecker) {
            return false;
        }

        $propertyPath = $right->propertyPath;
        if (null === $propertyPath) {
            return $this->authorizationChecker->isGranted($right->attributes);
        }

        $subject = $this->resolveSubject($object, $propertyPath);
        return $this->authorizationChecker->isGranted($right->attributes, $subject);
    }

    private function resolveSubject($entity, $propertyPath)
    {
        if ('this' === $propertyPath) {
            return $entity;
        }

        return $this->propertyAccessor->getValue($entity, $propertyPath);
    }

    private function convert(?string $type, $value)
    {
        if (null === $value) {
            return $value;
        }

        switch ($type) {
            case 'datetime':
            case 'date':
            case 'time':
                return new DateTime($value);
            default:
                return $value;
        }
    }

    private function isUpdateableByReference(PropertyMetadata $propertyMetadata, string $method)
    {
        if (
            CrudOperation::UPDATE === $method
            && null !== $propertyMetadata->getPuttable() && true === $propertyMetadata->getPuttable()->byReference
        ) {
            return true;
        }

        if (
            CrudOperation::CREATE === $method
            && null !== $propertyMetadata->getPostable() && true === $propertyMetadata->getPostable()->byReference
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
}
