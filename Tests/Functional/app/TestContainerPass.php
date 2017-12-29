<?php

namespace Dontdrinkandroot\RestBundle\Tests\Functional\app;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

class TestContainerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $services = [];//array_keys($container->findTaggedServiceIds('test.container', true));

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isPublic() && !$definition->isAbstract()) {
                $services[] = $id;
            }
        }

        $container->register('test.container', ServiceLocator::class)
            ->setPublic(true)
            ->addTag('container.service_locator')
            ->setArguments(
                [
                    array_combine(
                        $services,
                        array_map(
                            function (string $id): Reference {
                                return new Reference($id);
                            },
                            $services
                        )
                    )
                ]
            );
    }
}
