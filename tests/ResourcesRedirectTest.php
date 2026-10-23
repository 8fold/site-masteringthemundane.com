<?php

declare(strict_types=1);

namespace MTM\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourcesRedirectTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function legacyRoutes(): iterable
    {
        foreach (
            [
            'articles' => '/resources/',
            'tools-and-resources' => '/resources/',
            'articles/the-action-loop' => '/resources/the-action-loop/',
            'tools-and-resources/motivators' => '/resources/motivators/',
            'tools-and-resources/the-5-ps' => '/resources/the-5-ps/'
            ] as $old => $new
        ) {
            foreach (['', '/', '/index.php', '/index.php/'] as $suffix) {
                yield $old . $suffix => [$old . $suffix, $new];
            }
        }
    }

    #[DataProvider('legacyRoutes')]
    public function testLegacyRulesRedirectDirectlyBeforeSlashHandling(
        string $path,
        string $destination
    ): void {
        self::assertSame($destination, self::redirect($path));
    }

    public function testCanonicalAndUnknownRoutesDoNotMatchLegacyRules(): void
    {
        foreach (
            [
            'resources/', 'resources/the-action-loop/', 'resources/motivators/',
            'resources/the-5-ps/', 'articles/unknown/', 'articles-extra/',
            'tools-and-resources/unknown/', 'books/', 'coaching-and-mentoring/'
            ] as $path
        ) {
            self::assertNull(self::redirect($path), $path);
        }
    }

    private function redirect(string $path): ?string
    {
        $config = file_get_contents(dirname(__DIR__) . '/public/.htaccess');
        self::assertIsString($config);
        $beforeSlashHandling = explode('# Redirect Trailing Slashes', $config)[0];
        preg_match_all(
            '/^\s*RewriteRule (\S+) (\S+) \[R=301,L\]$/m',
            $beforeSlashHandling,
            $rules,
            PREG_SET_ORDER
        );
        foreach ($rules as $rule) {
            $pattern = '~' . $rule[1] . '~';
            if (preg_match($pattern, $path) === 1) {
                return preg_replace($pattern, $rule[2], $path);
            }
        }
        return null;
    }
}
