<?php declare(strict_types=1);

/*
 * This file is part of the PsyshBundle package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\PsyshBundle\Command;

use Psy\Output\ShellOutput;
use Psy\Shell;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Adrian PALMER <navitronic@gmail.com>
 * @author Théo FIDRY    <theo.fidry@gmail.com>
 */
final class PsyshCommand extends Command
{
    private Shell $psysh;

    public function __construct(Shell $psysh)
    {
        parent::__construct();

        $this->psysh = $psysh;
    }

    protected function configure(): void
    {
        $this->setDescription('Start PsySH for Symfony');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Reset input & output if they are the default ones used. Indeed
        // We call Psysh Application here which will do the necessary
        // bootstrapping.
        // If we don't we would force the regular Symfony Application
        // bootstrapping instead not allowing the Psysh one to kick in at all.
        if ($input instanceof ArgvInput) {
            $input = null;
        }

        if ($output instanceof ConsoleOutput) {
            $output = null;
        }

        return $this->psysh->run($input, $output);
    }
}
