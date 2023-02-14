<?php

/*
 * This file is part of the PsyshBundle package.
 *
 * (c) Jérôme Gangneux <jerome@gangneux.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fidry\PsyshBundle\Command;

use Fidry\PsyshBundle\Interactive;
use Psr\Container\ContainerInterface;
use Psy\Command\Command;
use Psy\Shell;
use Symfony\Bundle\FrameworkBundle\Command\BuildDebugContainerTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class ServiceCommand extends Command
{
    use BuildDebugContainerTrait;

    private static KernelInterface $kernel;
    private SymfonyStyle $symfonyStyle;
    /** @var array<int, string> */
    private array $servicesIds = [];

    public static function resolvedService(string $identifier): object
    {
        /** @var ContainerInterface $testContainer */
        $testContainer = self::$kernel->getContainer()->get('test.service_container');

        return $testContainer->get($identifier);
    }

    public function __construct(KernelInterface $kernel)
    {
        self::$kernel = $kernel;

        $this->computeServiceIds();

        parent::__construct('service');
    }

    protected function configure(): void
    {
        $this
            ->setName('service')
            ->setDefinition([
                new InputArgument('variable', InputArgument::REQUIRED, 'desc'),
                new InputArgument('equal', InputArgument::OPTIONAL, 'desc'),
                new InputArgument('serviceIdentifier', InputArgument::OPTIONAL, 'desc'),
            ])
            ->setDescription('Service command.')
            ->setHelp(
                <<<'HELP'
...
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->symfonyStyle = new SymfonyStyle($input, $output);

        /** @var Shell $shell */
        $shell = $this->getApplication();

        $variable = trim($input->getArgument('variable'), '$');
        $identifier = $input->getArgument('serviceIdentifier')
            ?? $input->getArgument('equal');
        $result = $this->findServiceId($identifier);

        if ($result) {
            $shell->addInput('$' . $variable . ' = resolved_service(\'' . $result . '\');');
        }

        return 0;
    }

    private function computeServiceIds(): void
    {
        $containerBuilder = $this->getContainerBuilder(self::$kernel);
        $this->servicesIds = array_filter(array_merge(
            array_keys($containerBuilder->getDefinitions()),
            array_keys($containerBuilder->getAliases()),
        ), fn (string $id) => !str_starts_with($id, '.'));
        unset($containerBuilder);
    }

    public function findServiceId(?string $identifier = null): ?string
    {
        /** @var ContainerInterface $container */
        $container = self::$kernel->getContainer()->get('test.service_container');

        // Perfect match
        if ($identifier && $container->has($identifier)) {
            $this->symfonyStyle->success(sprintf('Service "%s" found for identifier: %s', $identifier, $identifier));

            return $identifier;
        }

        $found = Interactive::find($this->servicesIds, $identifier, fn (string $identifier) => $container->has($identifier));
        if (null === $found) {
            $this->symfonyStyle->error(sprintf('No service found for identifier: %s', $identifier));

            return null;
        }

        if (1 === \count($found)) {
            $this->symfonyStyle->success(sprintf('Service "%s" found for identifier: %s', $found[0], $identifier));

            return $found[0];
        }

        $this->symfonyStyle->warning(sprintf('Found multiple services for identifier: %s', $identifier));
        $found[] = 'cancel';
        $candidate = $this->symfonyStyle->choice('Select the service you want', $found);
        if ('cancel' === $candidate) {
            $this->symfonyStyle->warning('Cancelled');

            return null;
        }

        $this->symfonyStyle->success(sprintf('Service "%s" found for identifier: %s', $candidate, $identifier));

        return $candidate;
    }
}
