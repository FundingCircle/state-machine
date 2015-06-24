<?php

namespace StateMachineBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class StateMachineExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $stateMachineFactory = $container->getDefinition('statemachine.factory');

        $historyListener = new Reference($config['history_listener']);

        $stateMachineFactory->replaceArgument(1, $historyListener);
        $stateMachineFactory->replaceArgument(2, $config['transition_class']);

        foreach ($config['state_machines'] as $stateMachine) {
            foreach ($stateMachine['transitions'] as &$transition) {
                foreach ($transition['guards'] as &$guard) {
                    $guard['callback'] = new Reference($guard['callback']);
                }
                foreach ($transition['pre_transitions'] as &$preTransition) {
                    $guard['callback'] = new Reference($preTransition['callback']);
                }
                foreach ($transition['post_transitions'] as &$postTransition) {
                    $guard['callback'] = new Reference($postTransition['callback']);
                }
            }

            $stateMachineFactory->addMethodCall('register', [$stateMachine]);
        }
    }
}