<?php

declare(strict_types=1);

namespace MTM\Tests;

use MTM\Marketing\UserInterface\PageMetadata;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PublicViewsHttpTest extends TestCase
{
    use HtmlDocumentAssertions;

    public function testCanonicalPublicViewsRespondWithOneHeadingAndUniqueIds(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
        self::assertIsResource($server, $errorMessage ?? 'Could not reserve a local server port.');
        $address = stream_socket_get_name($server, false);
        fclose($server);
        self::assertIsString($address);

        $port = substr($address, strrpos($address, ':') + 1);
        $documentRoot = dirname(__DIR__) . '/public';
        $process = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $documentRoot],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            dirname(__DIR__)
        );
        self::assertIsResource($process);
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        try {
            $baseUrl = 'http://127.0.0.1:' . $port;
            self::waitForServer($baseUrl);
            foreach (PageMetadata::ROUTES as $route) {
                $url = $baseUrl . $route;
                $context = stream_context_create([
                    'http' => [
                        'ignore_errors' => true,
                        'timeout' => 3,
                    ],
                ]);
                $html = file_get_contents($url, false, $context);
                self::assertIsString($html, 'Could not read response for ' . $route);
                $headers = http_get_last_response_headers();
                self::assertIsArray($headers, 'Could not read response headers for ' . $route);
                self::assertSame(
                    200,
                    self::statusCode($headers),
                    'Unexpected HTTP status for ' . $route
                );
                $contentType = self::headerValue($headers, 'Content-Type');
                $mediaType = strtolower(trim(explode(';', $contentType)[0]));
                self::assertSame('text/html', $mediaType, $route);
                $context = 'GET ' . $route;
                self::assertSinglePageHeading($html, $context);
                self::assertHeadingLevelsDoNotSkip($html, $context);
                self::assertUniqueIds($html, $context);
            }
        } finally {
            proc_terminate($process);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }
    }

    private static function waitForServer(string $baseUrl): void
    {
        $deadline = microtime(true) + 5;
        do {
            $response = @file_get_contents(
                $baseUrl . '/',
                false,
                stream_context_create(['http' => ['timeout' => 1]])
            );
            if (is_string($response)) {
                return;
            }
            usleep(50_000);
        } while (microtime(true) < $deadline);

        throw new RuntimeException('The isolated PHP HTTP server did not start.');
    }

    /** @param list<string> $headers */
    private static function statusCode(array $headers): int
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})\b/', $header, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    /** @param list<string> $headers */
    private static function headerValue(array $headers, string $name): string
    {
        foreach ($headers as $header) {
            if (str_starts_with(strtolower($header), strtolower($name) . ':')) {
                return trim(substr($header, strlen($name) + 1));
            }
        }

        return '';
    }
}
