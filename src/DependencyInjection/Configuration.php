<?php
declare(strict_types=1);

namespace MH1\CronBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mh1_cron');

        $cronjobServiceClassNode = new ScalarNodeDefinition('service');
        $cronjobServiceClassNode->defaultNull();

        $cronjobLogServiceClassNode = new ScalarNodeDefinition('log_service');
        $cronjobLogServiceClassNode->defaultNull();

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->append($cronjobServiceClassNode);
        $rootNode->append($cronjobLogServiceClassNode);

        return $treeBuilder;
    }
}
