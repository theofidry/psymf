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

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Adrian PALMER <navitronic@gmail.com>
 * @author Théo FIDRY    <theo.fidry@gmail.com>
 */
final class PsyshCommand extends Command
{
    private Application $psysh;

    public function __construct(Application $psysh)
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
        return $this->psysh->run($input, $output);
    }
}
