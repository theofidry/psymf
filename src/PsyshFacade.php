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
final class PsyshFacade implements ContainerAwareInterface
{
    private static Shell $shell;

    private static ContainerInterface $container;

    public static function init(): void
    {
        if (isset(self::$shell)) {
            return;
        }

        if (!isset(self::$container)) {
            throw new RuntimeException('Cannot initialize the facade without a container.');
        }

        self::$shell = self::$container->get('psysh.shell');
    }

    public static function debug(array $variables = [], $bind = null): void
    {
        self::init();

        $_variables = array_merge(self::$shell->getScopeVariables(), $variables);

        self::$shell::debug($_variables, $bind);
    }

    public function setContainer(ContainerInterface $container = null): void
    {
        self::$container = $container;
    }
}
