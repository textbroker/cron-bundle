<?php
declare(strict_types=1);

namespace MH1\CronBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
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

        $cronjobCheckIntervalNode = new ScalarNodeDefinition('check_interval');
        $cronjobCheckIntervalNode->defaultValue(1000);

        $cronjobExecutionTimeZoneNode = new ScalarNodeDefinition('execution_time_zone');
        $cronjobExecutionTimeZoneNode->defaultNull();

        $cronjobLockPrefixNode = new ScalarNodeDefinition('lock_prefix');
        $cronjobLockPrefixNode->defaultValue('');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->append($cronjobServiceClassNode);
        $rootNode->append($cronjobLogServiceClassNode);
        $rootNode->append($cronjobCheckIntervalNode);
        $rootNode->append($cronjobExecutionTimeZoneNode);
        $rootNode->append($cronjobLockPrefixNode);

        return $treeBuilder;
    }
}
