<?php

namespace StateMachineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    private static $stateTypes = ['initial', 'normal', 'final'];

    private static $defaultOptions = ['transaction' => true];

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('state_machine');
        $this->addGeneralSection($rootNode);
        $this->addStateMachinesSection($rootNode);

        return $treeBuilder;
    }

    protected function addGeneralSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('transition_class')->defaultValue('StateMachine\Transition\Transition')->end()
                ->scalarNode('state_accessor')->defaultValue('statemachine.state_accessor')->end()
                ->scalarNode('history_manager')->defaultValue('statemachine.history_manager')->end()
                ->scalarNode('template_layout')->defaultValue('StateMachineBundle::layout.html.twig')->end()
                ->scalarNode('db_driver')->defaultValue('orm')->end()
                ->booleanNode('profiler')->defaultValue(false)->end()
            ->end()
        ;
    }

    protected function addStateMachinesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('state_machines')
                ->useAttributeAsKey('array')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('object')
                                ->children()
                                    ->scalarNode('class')->cannotBeEmpty()->isRequired()->end()
                                    ->scalarNode('property')->cannotBeEmpty()->isRequired()->end()
                                ->end()
                            ->end()
                            ->scalarNode('history_class')->cannotBeEmpty()->isRequired()->end()
                            ->scalarNode('description')->defaultValue('')->end()
                            ->arrayNode('options')
                                ->prototype('scalar')->end()
                                ->defaultValue(self::$defaultOptions)
                            ->end()
                            ->arrayNode('states')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('type')
                                        ->cannotBeEmpty()
                                        ->defaultValue('normal')
                                        ->validate()
                                        ->ifNotInArray(self::$stateTypes)
                                        ->thenInvalid('Invalid state type "%s" type must be '.implode(',', self::$stateTypes))
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()->end()
                            ->arrayNode('transitions')->prototype('array')
                                ->children()
                                    ->arrayNode('from')
                                        ->prototype('variable')->end()
                                    ->end()
                                    ->arrayNode('to')
                                        ->prototype('variable')->end()
                                    ->end()
                                    ->scalarNode('event')
                                    ->end()
                                ->end()
                            ->end()->end()
                            ->arrayNode('guards')
                                ->prototype('array')
                                    ->children()
                                        ->variableNode('from')->end()
                                        ->variableNode('to')->end()
                                        ->scalarNode('callback')->end()
                                        ->scalarNode('method')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('pre_transitions')
                                ->prototype('array')
                                    ->children()
                                        ->variableNode('from')->end()
                                        ->variableNode('to')->end()
                                        ->scalarNode('callback')->end()
                                        ->scalarNode('method')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('post_transitions')
                                ->prototype('array')
                                    ->children()
                                        ->variableNode('from')->end()
                                        ->variableNode('to')->end()
                                        ->scalarNode('callback')->end()
                                        ->scalarNode('method')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('post_commits')
                                ->prototype('array')
                                    ->children()
                                        ->variableNode('from')->end()
                                        ->variableNode('to')->end()
                                        ->scalarNode('callback')->end()
                                        ->scalarNode('method')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('on_init')
                                ->children()
                                    ->scalarNode('callback')->end()
                                    ->scalarNode('method')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
            ->end();
    }
}
