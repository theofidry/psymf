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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Adrian PALMER <navitronic@gmail.com>
 * @author Théo FIDRY    <theo.fidry@gmail.com>
 */
final class PsyshCommand extends Command
{
    /**
     * @var Shell
     */
    private $shell;

    /**
     * @param Shell $shell
     */
    public function __construct(Shell $shell)
    {
        parent::__construct();

        $this->shell = $shell;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Start PsySH for Symfony');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return (int) $this->shell->run();
    }
}
