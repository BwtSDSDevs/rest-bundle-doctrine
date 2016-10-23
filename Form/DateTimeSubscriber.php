<?php

namespace Dontdrinkandroot\RestBundle\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DateTimeSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'onPreSetData'];
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $children = $form->all();
        $dateTimeFields = [];
        foreach ($children as $child) {
            $typeName = $child->getConfig()->getType()->getName();
            if ('datetime' === $typeName) {
                $dateTimeFields[] = $child->getName();
            }
        }

        foreach ($dateTimeFields as $dateTimeField) {
            $form->remove($dateTimeField);
        }

        foreach ($dateTimeFields as $dateTimeField) {
            $form->add($dateTimeField, DateTimeType::class, ['widget' => 'single_text']);
        }
    }
}
