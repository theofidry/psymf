<?php

/*
 * This file is part of the PsyshBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\PsyshBundle\DependencyInjection\Compiler;

use Psy\Command\Command as PsyshCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass allowing to add Psysh commands dynamically
 *
 * @author Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * @private
 */
final class AddPsyshCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('psysh.shell')) {
            return;
        }

        $commands = [];
        foreach ($container->findTaggedServiceIds('psysh.command') as $id => $attributes) {
            // Workaround to avoid Psysh commands to be registered as regular console commands
            // (conflict with service autoconfiguration as Psysh commands inherit from \Symfony\Component\Console\Command\Command as well
            // Note that this compiler pass must run with a higher priority than AddConsoleCommandPass to be efficient.
            $container->findDefinition($id)->clearTag('console.command');
            $commands[] = new Reference($id);
        }

        $shellRef = $container->findDefinition('psysh.shell');
        $shellRef->addMethodCall('addCommands', [$commands]);
    }
}
