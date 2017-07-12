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
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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
        
        // Register Psysh commands for service autoconfiguration (Symfony 3.3+)
        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration(Command::class)->addTag('psysh.command');
        }
    }
}
