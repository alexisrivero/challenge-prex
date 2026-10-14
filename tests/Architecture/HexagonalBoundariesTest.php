<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Executable version of the architecture rules.
 *
 * A diagram in a README rots; these tests fail the build the day somebody
 * injects an Eloquent model into a use case.
 */
final class HexagonalBoundariesTest extends TestCase
{
    private const string SOURCE_PATH = __DIR__.'/../../src';

    /** @var list<string> */
    private const array FRAMEWORK_NAMESPACES = [
        'Illuminate\\',
        'Laravel\\',
        'Symfony\\',
        'League\\',
        'GuzzleHttp\\',
        'Carbon\\',
    ];

    /** @var list<string> */
    private const array BOUNDED_CONTEXTS = ['Auth', 'Gifs', 'Audit', 'Shared'];

    #[Test]
    #[DataProvider('boundedContexts')]
    public function the_domain_layer_does_not_depend_on_the_framework(string $context): void
    {
        $violations = [];

        foreach ($this->importsOf($context, 'Domain') as $file => $imports) {
            foreach ($imports as $import) {
                foreach (self::FRAMEWORK_NAMESPACES as $forbidden) {
                    if (str_starts_with($import, $forbidden)) {
                        $violations[] = sprintf('%s imports %s', $file, $import);
                    }
                }
            }
        }

        self::assertSame([], $violations, 'The domain must stay framework agnostic.');
    }

    #[Test]
    #[DataProvider('boundedContexts')]
    public function the_domain_layer_does_not_depend_on_outer_layers(string $context): void
    {
        self::assertSame(
            [],
            $this->importsMatching($context, 'Domain', '/^Prex\\\\\w+\\\\(Infrastructure|Application)\\\\/'),
            'Dependencies must point inwards: the domain is the innermost layer.'
        );
    }

    #[Test]
    #[DataProvider('boundedContexts')]
    public function the_application_layer_does_not_depend_on_infrastructure(string $context): void
    {
        self::assertSame(
            [],
            $this->importsMatching($context, 'Application', '/^Prex\\\\\w+\\\\Infrastructure\\\\/'),
            'Use cases talk to ports, never to adapters.'
        );
    }

    #[Test]
    #[DataProvider('boundedContexts')]
    public function the_application_layer_does_not_depend_on_the_framework(string $context): void
    {
        $violations = [];

        foreach ($this->importsOf($context, 'Application') as $file => $imports) {
            foreach ($imports as $import) {
                foreach (self::FRAMEWORK_NAMESPACES as $forbidden) {
                    if (str_starts_with($import, $forbidden)) {
                        $violations[] = sprintf('%s imports %s', $file, $import);
                    }
                }
            }
        }

        self::assertSame([], $violations, 'Use cases must be runnable outside Laravel.');
    }

    #[Test]
    #[DataProvider('boundedContexts')]
    public function a_bounded_context_only_knows_itself_and_the_shared_kernel(string $context): void
    {
        $allowed = sprintf('/^Prex\\\\(%s|Shared)\\\\/', preg_quote($context, '/'));
        $violations = [];

        foreach ($this->importsOf($context) as $file => $imports) {
            foreach ($imports as $import) {
                if (str_starts_with($import, 'Prex\\') && preg_match($allowed, $import) !== 1) {
                    $violations[] = sprintf('%s imports %s', $file, $import);
                }
            }
        }

        self::assertSame(
            [],
            $violations,
            'Contexts communicate through the shared kernel, not directly with each other.'
        );
    }

    #[Test]
    public function every_port_lives_in_the_domain_layer(): void
    {
        $interfacesOutsideDomain = [];

        foreach ($this->phpFiles(self::SOURCE_PATH) as $file) {
            $contents = (string) file_get_contents($file->getPathname());
            $relative = $this->relativePath($file);

            if (preg_match('/^\s*(final )?interface \w+/m', $contents) !== 1) {
                continue;
            }

            if (! str_contains($relative, '/Domain/')) {
                $interfacesOutsideDomain[] = $relative;
            }
        }

        self::assertSame(
            [],
            $interfacesOutsideDomain,
            'Ports are owned by the domain; adapters implement them from the outside.'
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function boundedContexts(): array
    {
        return array_combine(
            self::BOUNDED_CONTEXTS,
            array_map(static fn (string $context): array => [$context], self::BOUNDED_CONTEXTS)
        );
    }

    /**
     * @return list<string> violations, as "file imports FQCN"
     */
    private function importsMatching(string $context, ?string $layer, string $pattern): array
    {
        $violations = [];

        foreach ($this->importsOf($context, $layer) as $file => $imports) {
            foreach ($imports as $import) {
                if (preg_match($pattern, $import) === 1) {
                    $violations[] = sprintf('%s imports %s', $file, $import);
                }
            }
        }

        return $violations;
    }

    /**
     * @return array<string, list<string>> file => imported FQCNs
     */
    private function importsOf(string $context, ?string $layer = null): array
    {
        $path = self::SOURCE_PATH.'/'.$context.($layer === null ? '' : '/'.$layer);

        if (! is_dir($path)) {
            return [];
        }

        $imports = [];

        foreach ($this->phpFiles($path) as $file) {
            $contents = (string) file_get_contents($file->getPathname());

            preg_match_all('/^use ([^;]+);$/m', $contents, $matches);

            $imports[$this->relativePath($file)] = array_map(
                static fn (string $import): string => trim(explode(' as ', $import)[0]),
                $matches[1]
            );
        }

        return $imports;
    }

    /**
     * @return list<SplFileInfo>
     */
    private function phpFiles(string $path): array
    {
        $files = [];

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file;
            }
        }

        return $files;
    }

    private function relativePath(SplFileInfo $file): string
    {
        return str_replace((string) realpath(self::SOURCE_PATH.'/..'), '', (string) $file->getRealPath());
    }
}
