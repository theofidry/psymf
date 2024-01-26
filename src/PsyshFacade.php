<?php declare(strict_types=1);

/*
 * This file is part of the PsyshBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\PsyshBundle;

use Psy\Shell;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function array_merge;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class PsyshFacade
{
    private static Shell $shell;

    public static function init(): void
    {
        // noop ... keeping the method as is for backward compatibility
    }

    public static function debug(array $variables = [], $bind = null): void
    {
        if (!isset(self::$shell)) {
            throw new RuntimeException('Cannot initialize the facade without shell.');
        }

        $_variables = array_merge(self::$shell->getScopeVariables(), $variables);

        \Psy\debug($_variables, $bind);
    }

    public function setShell(Shell $shell): void
    {
        self::$shell = $shell;
    }
}
