<?php
declare(strict_types = 1);

use \Symfony\Component\HttpKernel\Kernel;
use \Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function __construct()
    {
        $env = getenv('APP_ENVIRONMENT') ?: 'prod';
        $debug = filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);

        if ($debug === true) {
            \Symfony\Component\Debug\Debug::enable();
        }

        parent::__construct($env, $debug);
    }

    public function registerBundles(): array
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \PommProject\PommBundle\PommBundle(),
            new \PommProject\ApiPlatform\PommApiPlatformBundle(),
            new \ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle(),
            new \AppBundle\AppBundle(),
        ];

        if ($this->getEnvironment() === 'dev') {
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function getRootDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return __DIR__ . '/../var/cache/' . $this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return __DIR__ . '/../var/logs/' . $this->getEnvironment();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
