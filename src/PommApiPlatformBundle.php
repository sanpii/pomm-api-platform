<?php
declare(strict_types = 1);

namespace PommProject\ApiPlatform;

use PommProject\ApiPlatform\DependencyInjection\Compiler\ResourceClassResolverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\HttpKernel\Bundle\Bundle;

class PommApiPlatformBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ResourceClassResolverPass());
    }
}
