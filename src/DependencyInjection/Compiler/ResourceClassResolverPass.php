<?php
/**
 * This file is part of the pomm-api-platform-bridge package.
 *
 */

namespace PommProject\ApiPlatform\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Mikael Paris <stood86@gmail.com>
 */
final class ResourceClassResolverPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('api_platform.resource_class_resolver')) {
            $definition = new Definition('PommProject\ApiPlatform\ResourceClassResolver');
            $definition->addArgument(new Reference('api_platform.metadata.resource.name_collection_factory'));

            $container->setDefinition('api_platform.resource_class_resolver', $definition);
        }
    }
}
