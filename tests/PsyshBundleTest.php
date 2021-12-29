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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @coversNothing
 *
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
class PsyshBundleTest extends KernelTestCase
{
    /**
     * Test that the bundle loads and compiles.
     */
    public function testServicesLoading(): void
    {
        static::bootKernel();

        $this->assertInstanceOf(
            Shell::class,
            static::getContainer()->get('psysh.shell'),
        );
    }
}
