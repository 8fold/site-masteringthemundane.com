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
        $xpath = $this->page();
        self::assertSame(1, $this->countMatches($xpath, '//main[@id="main"][@tabindex="-1"]'));
        self::assertSame(1, $this->countMatches($xpath, '//a[@href="#main"][@is="skip-link"]'));
        self::assertSame('Coaching and Mentoring', $xpath->evaluate('string(//header/h1)'));
        self::assertSame(0, $this->countMatches($xpath, '//header/h1/*'));
        self::assertNotSame('', trim($this->text($xpath, 'string(//main)')));
        self::assertSame(
            [
                '/', '/coaching-and-mentoring/', '/services/',
                '/books/', '/resources/'
            ],
            $this->texts($xpath, '//nav[@aria-label="Menu"]//a/@href')
        );
        self::assertSame(
            ['Home', 'Coaching', 'Services', 'Books', 'Resources'],
            $this->texts($xpath, '//nav[@aria-label="Menu"]//a')
        );
        self::assertSame(0, $this->countMatches($xpath, '//nav[@aria-label="Read"]'));
        $readingHistoryLink = '//header/a[@href="https://apps.mastering-the-mundane.com"]';
        self::assertSame(
            'Apps',
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
        self::assertSame(1, $this->countMatches($xpath, '//nav//a[@aria-current]'));
        self::assertSame(
            '/coaching-and-mentoring/',
            $xpath->evaluate('string(//nav//a[@aria-current="page"]/@href)')
        );
        self::assertSame(
            1,
            $this->countMatches(
                $xpath,
                '//button[@type="button"][@popovertarget="primary-navigation"]'
            )
        );
        self::assertSame(
            1,
            $this->countMatches($xpath, '//div[@id="primary-navigation"][@popover="auto"]')
        );
        self::assertSame(
            '© 2023–' . date('Y') . ' Mastering the Mundane',
            $this->text($xpath, 'string(//footer)')
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
            $this->countMatches(
                $this->page(),
                '//nav[@aria-label="Menu"]//a[starts-with(@href, "/#")]'
            )
        );
    }
    public function testBookingAndServicesRemainAccessibleWithoutClientOrAccountDependencies(): void
    {
        $xpath = $this->page();
        $booking = '//main//a[@href="https://cal.com/mastering-the-mundane/introduction"]';
        self::assertSame(1, $this->countMatches($xpath, $booking));
        self::assertNotSame('', trim($this->text($xpath, 'string(' . $booking . ')')));
        self::assertSame('/services/', $xpath->evaluate('string(//main//a[not(@target)]/@href)'));
        self::assertSame(0, $this->countMatches($xpath, '//form|//iframe|//script[@src]'));
        self::assertSame(0, $this->countMatches($xpath, '//link[@rel="canonical"]'));
        self::assertSame(1, $this->countMatches($xpath, '//script'));
        self::assertSame(PHP_SESSION_NONE, session_status());
    }

    public function testEveryRenderedHttpAnchorFollowsTheHostnamePolicy(): void
    {
        RenderedAnchorAssertions::assertHttpHostPolicy(
            $this->page(),
            'https://mastering-the-mundane.com/coaching-and-mentoring/'
        );
    }

    public function testMetadataRetainsExistingValuesAndPilotAssetsAreLocalAndVersioned(): void
    {
        $xpath = $this->page();
        self::assertSame(
            'Coaching and Mentoring | Mastering the Mundane',
            $xpath->evaluate('string(//title)')
        );
        $description = $this->text($xpath, 'string(//meta[@name="description"]/@content)');
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
            'https://mastering-the-mundane.com/media/poster.png',
            $xpath->evaluate('string(//meta[@property="og:image"]/@content)')
        );
        self::assertSame(1, $this->countMatches($xpath, '//link[@rel="stylesheet"]'));
        self::assertMatchesRegularExpression(
            '~\A/css/reading\.css\?v=[a-f0-9]{64}\z~',
            $this->text($xpath, 'string(//link[@rel="stylesheet"]/@href)')
        );
        self::assertSame(0, $this->countMatches($xpath, '//header//img'));
        self::assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'CoachingService',
            'provider' => [
                '@type' => 'Person',
                'name' => 'Joshua Bruce',
                'url' => 'https://itsjoshbruce.com'
            ],
            'areaServed' => 'Remote'
        ], json_decode($this->text($xpath, 'string(//script)'), true, flags: JSON_THROW_ON_ERROR));
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
