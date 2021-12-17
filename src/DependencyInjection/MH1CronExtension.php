<?php
declare(strict_types=1);

namespace MH1\CronBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;

class MH1CronExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array|string[][] $configs
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('parameters.yaml');
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // overwrite parameter, if custom cronjob service class is provided
        if (isset($config['service'])) {
            $container->setParameter('cronjob.service', $config['service']);
        }

        // overwrite parameter, if custom cronjob log service class is provided
        if (isset($config['log_service'])) {
            $container->setParameter('cronjob.log.service', $config['log_service']);
        }

        // overwrite parameter, if custom sleep time is provided
        if (isset($config['check_interval'])) {
            $container->setParameter('cronjob.check_interval', $config['check_interval']);
        }

        // overwrite parameter, if custom timezone is provided
        if (isset($config['execution_time_zone'])) {
            $container->setParameter('cronjob.execution_time_zone', $config['execution_time_zone']);
        }

        // overwrite parameter, if set
        if (isset($config['lock_prefix'])) {
            $container->setParameter('cronjob.lock_prefix', $config['lock_prefix']);
        }

        // overwrite parameter, if set
        if (isset($config['php_executable_path'])) {
            $container->setParameter('cronjob.php_executable_path', $config['php_executable_path']);
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        /** @var array<string, BundleInterface> $bundles */
        $bundles = $container->getParameter('kernel.bundles');
        // check if doctrine bundle is registered
        if (!isset($bundles['DoctrineBundle'])) {
            return;
        }

        $filePath = __DIR__ . '/../../config/packages/doctrine.yaml';
        $config = Yaml::parseFile($filePath);
        $container->prependExtensionConfig('doctrine', $config['doctrine']);
    }
}
