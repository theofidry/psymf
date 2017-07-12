<?php

/*
 * This file is part of the PsyshBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\PsyshBundle;

use Fidry\PsyshBundle\DependencyInjection\Compiler\AddPsyshCommandPass;
use Psy\Command\Command;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Adrian PALMER <navitronic@gmail.com>
 * @author Théo FIDRY    <theo.fidry@gmail.com>
 */
final class PsyshBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        $this->container->get('psysh.facade');
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // Ensure that AddPsyshCommandPass runs before AddConsoleCommandPass to avoid autoconfiguration conflicts.
        $container->addCompilerPass(new AddPsyshCommandPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
    }
}
