<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMNode;
use DOMXPath;
use MTM\Marketing\UserInterface\CoachingPage;
use PHPUnit\Framework\TestCase;

final class CoachingPageTest extends TestCase
{
    public function testLandmarksHeadingsAndNativeMenuNavigation(): void
    {
        $xpath = self::page();
        self::assertSame(1, self::countMatches($xpath, '//main[@id="main"][@tabindex="-1"]'));
        self::assertSame(1, self::countMatches($xpath, '//a[@href="#main"][@is="skip-link"]'));
        self::assertSame('Coaching and Mentoring', $xpath->evaluate('string(//header/h1)'));
        self::assertSame(0, self::countMatches($xpath, '//header/h1/*'));
        self::assertNotSame('', trim(self::text($xpath, 'string(//main)')));
        self::assertSame(
            [
                '/', '/coaching-and-mentoring/', '/services/',
                '/resources/', '/books/'
            ],
            self::texts($xpath, '//nav[@aria-label="Menu"]//a/@href')
        );
        self::assertSame(
            ['Home', 'Coaching', 'Services', 'Resources', 'Books'],
            self::texts($xpath, '//nav[@aria-label="Menu"]//a')
        );
        self::assertSame(0, self::countMatches($xpath, '//nav[@aria-label="Read"]'));
        self::assertSame(
            1,
            self::countMatches(
                $xpath,
                '//header[@is="nav-header"]/nav[@is="nav-primary"]'
                    . '/div[@is="nav-panel"][@id="primary-navigation"][@popover="auto"]'
            )
        );
        $css = file_get_contents(dirname(__DIR__) . '/public/assets/css/reading.css');
        self::assertIsString($css);
        self::assertStringNotContainsString('flex: 0 0 5em', $css);
        self::assertStringNotContainsString('marketing-header', $css);
        self::assertStringNotContainsString('menu-picker', $css);
        self::assertStringNotContainsString('navigation-panel', $css);
        self::assertStringContainsString(
            '[is="nav-header"] > a > .new-tab-tail {' . "\n"
                . '  text-decoration: inherit;',
            $css
        );
        self::assertStringContainsString(
            '[is="nav-panel"] a {' . "\n"
                . '  display: block;' . "\n"
                . '  min-height: 44px;' . "\n"
                . '  padding-block: calc(var(--space-2xs) + 2px);',
            $css
        );
        self::assertStringContainsString(
            'nav a[aria-current="page"] {' . "\n"
                . '  font-weight: var(--font-weight-bold);',
            $css
        );
        $readingHistoryLink = '//header/a[@href="https://apps.mastering-the-mundane.com"]';
        self::assertSame(
            'Apps (opens in a new tab)',
            $xpath->evaluate('string(' . $readingHistoryLink . ')')
        );
        self::assertSame(
            '_blank',
            $xpath->evaluate('string(' . $readingHistoryLink . '/@target)')
        );
        self::assertSame(
            'noopener noreferrer',
            $xpath->evaluate('string(' . $readingHistoryLink . '/@rel)')
        );
        self::assertSame(1, self::countMatches($xpath, '//nav//a[@aria-current]'));
        self::assertSame(
            '/coaching-and-mentoring/',
            $xpath->evaluate('string(//nav//a[@aria-current="page"]/@href)')
        );
        self::assertSame(
            1,
            self::countMatches(
                $xpath,
                '//button[@type="button"][@popovertarget="primary-navigation"]'
            )
        );
        self::assertSame(
            1,
            self::countMatches($xpath, '//div[@id="primary-navigation"][@popover="auto"]')
        );
        self::assertSame(
            '© 2023–' . date('Y') . ' Mastering the Mundane',
            self::text($xpath, 'string(//footer)')
        );
    }

    public function testHomeDomainsRemainAvailableWithoutMenuShortcuts(): void
    {
        $home = \MTM\Marketing\UserInterface\HomePage::render();
        foreach (['time', 'people', 'space', 'money'] as $id) {
            self::assertStringContainsString('id="' . $id . '"', $home);
        }
        self::assertSame(
            0,
            self::countMatches(
                self::page(),
                '//nav[@aria-label="Menu"]//a[starts-with(@href, "/#")]'
            )
        );
    }
    public function testBookingAndServicesRemainAccessibleWithoutClientOrAccountDependencies(): void
    {
        $xpath = self::page();
        $booking = '//main//a[@href="https://cal.com/mastering-the-mundane/introduction"]';
        self::assertSame(1, self::countMatches($xpath, $booking));
        self::assertNotSame('', trim(self::text($xpath, 'string(' . $booking . ')')));
        self::assertSame('/services/', $xpath->evaluate('string(//main//a[not(@target)]/@href)'));
        self::assertSame(0, self::countMatches($xpath, '//form|//iframe|//script[@src]'));
        self::assertSame(0, self::countMatches($xpath, '//link[@rel="canonical"]'));
        self::assertSame(1, self::countMatches($xpath, '//script'));
        self::assertSame(PHP_SESSION_NONE, session_status());
    }

    public function testEveryRenderedHttpAnchorFollowsTheHostnamePolicy(): void
    {
        RenderedAnchorAssertions::assertHttpHostPolicy(
            self::page(),
            'https://mastering-the-mundane.com/coaching-and-mentoring/'
        );
    }

    public function testMetadataRetainsExistingValuesAndPilotAssetsAreLocalAndVersioned(): void
    {
        $xpath = self::page();
        self::assertSame(
            'Coaching and Mentoring | Mastering the Mundane',
            $xpath->evaluate('string(//title)')
        );
        $description = self::text($xpath, 'string(//meta[@name="description"]/@content)');
        self::assertStringStartsWith(
            'Josh Bruce has spent his life making peace with time, space, and money',
            $description
        );
        self::assertSame(
            $description,
            $xpath->evaluate('string(//meta[@property="og:description"]/@content)')
        );
        self::assertSame(
            'mastering-the-mundane.com',
            $xpath->evaluate('string(//meta[@property="og:url"]/@content)')
        );
        self::assertSame(
            'https://mastering-the-mundane.com',
            $xpath->evaluate('string(//meta[@property="twitter:url"]/@content)')
        );
        self::assertSame(
            'https://mastering-the-mundane.com/assets/media/poster.png',
            $xpath->evaluate('string(//meta[@property="og:image"]/@content)')
        );
        self::assertSame(1, self::countMatches($xpath, '//link[@rel="stylesheet"]'));
        self::assertMatchesRegularExpression(
            '~\A/assets/css/reading\.min\.css\?v=[a-f0-9]{16}\z~',
            self::text($xpath, 'string(//link[@rel="stylesheet"]/@href)')
        );
        self::assertSame(0, self::countMatches($xpath, '//header//img'));
        self::assertSame(
            \MTM\Marketing\StructuredData::forRoute('/coaching-and-mentoring/'),
            self::text($xpath, 'string(//script)')
        );
    }

    private function page(): DOMXPath
    {
        // HTML5 landmarks are valid; the legacy DOM parser otherwise reports them as unknown tags.
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(CoachingPage::render()));
            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    /**
     * @return list<string>
     */
    private function texts(DOMXPath $xpath, string $query): array
    {
        $result = [];
        $nodes = $xpath->query($query);
        self::assertNotFalse($nodes);
        foreach ($nodes as $node) {
            self::assertInstanceOf(DOMNode::class, $node);
            $result[] = $node->textContent;
        }
        return $result;
    }

    private function countMatches(DOMXPath $xpath, string $query): int
    {
        $nodes = $xpath->query($query);
        self::assertNotFalse($nodes);
        return $nodes->length;
    }

    private function text(DOMXPath $xpath, string $query): string
    {
        $value = $xpath->evaluate($query);
        self::assertIsString($value);
        return $value;
    }
}
