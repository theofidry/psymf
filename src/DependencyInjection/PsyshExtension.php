<?php

/*
 * This file is part of the PsyshBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\PsyshBundle\DependencyInjection;

use Psy\Command\Command;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class PsyshExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../../resources/config'));
        $loader->load('services.xml');
        if (class_exists(TestContainer::class) && !$container->has('test.service_container')) {
            $loader->load('test.xml');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        foreach ($config['variables'] as $name => &$value) {
            if (is_string($value) && $value[0] === '@') {
                $value = new Reference(substr($value, 1));
            }
        }
        $containerId = $container->has('test.service_container') ? 'test.service_container' : 'service_container';
        $container->findDefinition('psysh.shell')
            ->addMethodCall('setScopeVariables', [$config['variables'] + [
                'container' => new Reference($containerId),
                'kernel' => new Reference('kernel'),
                'self' => new Reference('psysh.shell'),
                'parameters' => new Expression(sprintf("service('%s').getParameterBag().all()", $containerId))
            ]]);
        
        // Register Psysh commands for service autoconfiguration (Symfony 3.3+)
        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration(Command::class)->addTag('psysh.command');
        }
    }
}
