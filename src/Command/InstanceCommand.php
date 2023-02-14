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
use Psy\Command\Command;
use Psy\Shell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class InstanceCommand extends Command
{
    private SymfonyStyle $symfonyStyle;

    /**
     * @param class-string $className
     */
    public static function resolvedClassName($className): object
    {
        $reflexion = new \ReflectionClass($className);
        $construct = $reflexion->getConstructor();
        if ($construct && $construct->getNumberOfRequiredParameters() > 0) {
            $args = self::methodToString($construct);
            var_dump('TODO warning this class need some non-optional parameters to be instanced: ' . implode(', ', $args));
            var_dump('Returning the class name instead.');
            throw new \Exception('');
        }

        return new $className();
    }

    public function __construct()
    {
        $this->forceLoadAllProjectClasses();

        parent::__construct('instance');
    }

    protected function configure(): void
    {
        $this
            ->setName('instance')
            ->setDefinition([
                new InputArgument('variable', InputArgument::REQUIRED, 'desc'),
                new InputArgument('equal', InputArgument::OPTIONAL, 'desc'),
                new InputArgument('className', InputArgument::OPTIONAL, 'desc'),
            ])
            ->setDescription('Instance command.')
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
        $identifier = $input->getArgument('className')
            ?? $input->getArgument('equal');
        $result = $this->findFullyQualifiedClassName($identifier);

        if ($result) {
            try {
                $this->resolvedClassName($result);
                $shell->addInput('$' . $variable . ' = resolved_class(\'' . $result . '\');');
            } catch (\Exception $e) {
                $shell->addInput('$' . $variable . '_class = \'' . $result . '\';');
            }
        }

        return 0;
    }

    /**
     * @return array<string>
     */
    private static function methodToString(\ReflectionMethod $method): array
    {
        return array_map(fn (\ReflectionParameter $parameter) => trim(
            ($parameter->getType() ?? '') .
            ' $' . $parameter->getName() .
            ($parameter->isOptional() ? ' = ' . $parameter->getDefaultValue() : '')
        ), $method->getParameters());
    }

    /**
     * @return ?class-string
     */
    public function findFullyQualifiedClassName(?string $identifier = null)
    {
        /** @var array<class-string> $classes */
        $classes = array_map(fn (string $name) => '\\' . trim($name, '\\'), get_declared_classes());
        /** @var ?array<class-string> $found */
        $found = Interactive::find(
            $classes,
            $identifier,
            fn (string $identifier) => class_exists($identifier),
        );

        if (null === $found) {
            $this->symfonyStyle->error(sprintf('No class found for identifier: %s', $identifier));

            return null;
        }

        if (1 === \count($found)) {
            $this->symfonyStyle->success(sprintf('Class "%s" found for identifier: %s', $found[0], $identifier));

            return $found[0];
        }

        $this->symfonyStyle->warning(sprintf('Found multiple classes for identifier: %s', $identifier));
        $found[] = 'cancel';
        $candidate = $this->symfonyStyle->choice('Select the class you want', $found);
        if ('cancel' === $candidate) {
            $this->symfonyStyle->warning('Cancelled');

            return null;
        }

        $this->symfonyStyle->success(sprintf('Class "%s" found for identifier: %s', $candidate, $identifier));

        return $candidate;
    }

    private function forceLoadAllProjectClasses(): void
    {
        var_dump('forceLoadAllProjectClasses');
        $composerFile = $this->findProjectDir();
        if (!$composerFile) {
            var_dump('TODO Warning: composer file not found.');

            return;
        }

        $composerPath = \dirname($composerFile->getRealPath());
        $composerData = json_decode((string) file_get_contents($composerFile->getRealPath()), true);
        if (false === $composerData) {
            var_dump('TODO Warning: composer data corrupted.');

            return;
        }

        $autoloadPsr4 = $composerData['autoload']['psr-4'] ?? [];

        foreach ($autoloadPsr4 as $namespace => $src) {
            $currentPath = $composerPath . '/' . $src;
            foreach ((new Finder())->in($currentPath)->files()->name('*.php')->reverseSorting() as $file) {
                $relativePath = (string) preg_replace('@^' . $currentPath . '@', '', $file->getRealPath());
                $relativeDirectory = trim(\dirname($relativePath), \DIRECTORY_SEPARATOR);
                $currentNamespace = str_replace(\DIRECTORY_SEPARATOR, '\\', $relativeDirectory);
                $currentNamespace = mb_strlen($currentNamespace) > 0 ? $currentNamespace . '\\' : '';
                $fullClassName = trim($namespace, '\\') . '\\' . $currentNamespace . $file->getFilenameWithoutExtension();
                try {
                    class_exists($fullClassName);
                } catch (\Exception $e) {
                    // No need user feedback here: var_dump($e->getMessage());
                }
            }
        }
    }

    /**
     * @return false|SplFileInfo
     */
    private function findProjectDir()
    {
        // __DIR__ = Command / .. = src / .. = psysh-bundle / .. = vendor
        $dir = (string) realpath(__DIR__ . '/../../../');
        while ($dir !== \dirname($dir)) {
            $files = (new Finder())->depth('== 0')->in($dir)->files()->name('composer.json');
            if ($files->count() > 0) {
                $results = iterator_to_array($files->getIterator());

                return current($results);
            }
            $dir = \dirname($dir); // Move up one level
        }

        return false;
    }
}
