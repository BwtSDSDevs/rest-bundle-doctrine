<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dontdrinkandroot\RestBundle\Serialization;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use JMS\Serializer\Context as JMSContext;
use JMS\Serializer\DeserializationContext as JMSDeserializationContext;
use JMS\Serializer\SerializationContext as JMSSerializationContext;
use JMS\Serializer\SerializerInterface;

/**
 * @deprecated
 */
class JMSSerializerAdapter implements Serializer
{
    /**
     * @internal
     */
    const SERIALIZATION = 0;
    /**
     * @internal
     */
    const DESERIALIZATION = 1;

    private $serializer;

    /**
     * @var IncludesExclusionStrategy
     */
    private $includesExclusionStrategy;

    public function __construct(SerializerInterface $serializer, IncludesExclusionStrategy $includesExclusionStrategy)
    {
        $this->serializer = $serializer;
        $this->includesExclusionStrategy = $includesExclusionStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, Context $context)
    {
        $context = $this->convertContext($context, self::SERIALIZATION);

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, Context $context)
    {
        $context = $this->convertContext($context, self::DESERIALIZATION);

        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    /**
     * @param Context $context
     * @param int     $direction {@see self} constants
     *
     * @return JMSContext
     */
    private function convertContext(Context $context, $direction)
    {
        if ($direction === self::SERIALIZATION) {
            $jmsContext = JMSSerializationContext::create();
        } else {
            $jmsContext = JMSDeserializationContext::create();
            $maxDepth = $context->getMaxDepth(false);
            if (null !== $maxDepth) {
                for ($i = 0; $i < $maxDepth; ++$i) {
                    $jmsContext->increaseDepth();
                }
            }
        }

        foreach ($context->getAttributes() as $key => $value) {
            $jmsContext->attributes->set($key, $value);
        }

        if (null !== $context->getVersion()) {
            $jmsContext->setVersion($context->getVersion());
        }
        if (null !== $context->getGroups()) {
            $jmsContext->setGroups($context->getGroups());
        }
        if (null !== $context->getMaxDepth(false) || null !== $context->isMaxDepthEnabled()) {
            $jmsContext->enableMaxDepthChecks();
        }
        if (null !== $context->getSerializeNull()) {
            $jmsContext->setSerializeNull($context->getSerializeNull());
        }

        $jmsContext->addExclusionStrategy($this->includesExclusionStrategy);

        return $jmsContext;
    }
}
