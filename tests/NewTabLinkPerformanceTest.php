<?php

declare(strict_types=1);

namespace MTM\Tests;

use MTM\Marketing\UserInterface\NewTabLink;
use PHPUnit\Framework\TestCase;

final class NewTabLinkPerformanceTest extends TestCase
{
    public function testTwentyNewTabLinksAreDecoratedWithinOneMillisecondOnAverage(): void
    {
        $html = '';
        for ($i = 0; $i < 20; $i++) {
            $html .= '<p>Item ' . $i . ': <a href="https://example.com/' . $i
                . '" target="_blank" rel="noopener noreferrer">Read more ' . $i . '</a></p>';
        }

        for ($i = 0; $i < 10; $i++) {
            NewTabLink::decorate($html);
        }

        $iterations = 100;
        $started = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            NewTabLink::decorate($html);
        }
        $averageNanoseconds = (hrtime(true) - $started) / $iterations;

        self::assertLessThan(
            1_000_000,
            $averageNanoseconds,
            sprintf(
                'Average decoration time was %.3f ms; the budget is 1 ms.',
                $averageNanoseconds / 1_000_000
            )
        );
    }
}
