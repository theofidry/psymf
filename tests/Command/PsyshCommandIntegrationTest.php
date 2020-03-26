<?php

/*
 * This file is part of the PsyshBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\PsyshBundle\Command;

use Psy\Shell;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 *
 * @coversNothing
 */
class PsyshCommandIntegrationTest extends KernelTestCase
{
    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var PsyshCommand
     */
    private $command;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->shell = self::$container->get('psysh.shell');
        $this->command = self::$container->get('psysh.command.shell_command');
    }

    public function testScopeVariables(): void
    {
        $this->assertEqualsCanonicalizing(
            [
                'container',
                'kernel',
                'parameters',
                '_',
                'self',
            ],
            array_keys($this->shell->getScopeVariables()),
            'Expected shell service to have scope variables'
        );
    }

    public function testContainerInstance(): void
    {
        $container = $this->shell->getScopeVariable('container');

        $this->assertInstanceOf(TestContainer::class, $container);
    }

    public function testFindShell(): void
    {
        $application = new Application(self::$kernel);
        $application->add($this->command);
        $application->find('psysh');

        $this->addToAssertionCount(1);
    }
}
