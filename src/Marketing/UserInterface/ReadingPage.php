<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use Eightfold\HtmlBuilder\Document;
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\Components\Copyright;
use Eightfold\HtmlBuilder\Components\PageTitle;

final class ReadingPage
{
    /** @param non-empty-list<string> $titles The page's title, then each ancestor's. */
    public static function render(
        string $route,
        array $titles,
        string $description
    ): string {
        // HtmlBuilder writes text and attribute values verbatim.
        $escape = static fn(string $text): string =>
            htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $titles = array_map($escape, $titles);
        $description = $escape($description);
        $heading = $titles[0];
        $pageTitle = PageTitle::create($titles);
        $title = implode(' | ', $titles);
        $keywords = 'Time Management, Self Management, Life Management, Life Coaching, '
            . 'Productivity Coaching, Financial Coaching, Professional Organization, '
            . 'bookkeeping, accoutability partner, digital organization, onsite, on-site, '
            . 'remote, hybrid, josh, bruce, josh bruce';
        $domain = 'mastering-the-mundane.com';
        $url = 'https://' . $domain;
        $poster = $url . '/media/poster.png';
        $head = [
            self::meta('name', 'viewport', 'width=device-width,initial-scale=1'),
            self::link('apple-touch-icon', '/favicons/apple-touch-icon.png')->prop('sizes 180x180'),
            self::link('icon', '/favicons/favicon-32x32.png')->props(
                'rel icon',
                'type image/png',
                'sizes 32x32',
                'href /favicons/favicon-32x32.png'
            ),
            self::link('icon', '/favicons/favicon-16x16.png')->props(
                'rel icon',
                'type image/png',
                'sizes 16x16',
                'href /favicons/favicon-16x16.png'
            ),
            self::link('manifest', '/favicons/site.webmanifest'),
            self::link('mask-icon', '/favicons/safari-pinned-tab.svg')->prop('color #000000'),
            self::link('shortcut icon', '/favicons/favicon.ico'),
            self::meta('name', 'msapplication-TileColor', '#ffffff'),
            self::meta('name', 'msapplication-config', '/favicons/browserconfig.xml'),
            self::meta('name', 'theme-color', '#ffffff'),
            self::meta('name', 'description', $description),
            self::meta('name', 'keywords', $keywords),
            self::meta('property', 'og:url', $domain),
            self::meta('property', 'og:type', 'website'),
            self::meta('property', 'og:title', $title),
            self::meta('property', 'og:description', $description),
            self::meta('property', 'og:image', $poster),
            self::meta('name', 'twitter:card', 'summary_large_image'),
            self::meta('property', 'twitter:domain', $domain),
            self::meta('property', 'twitter:url', $url),
            self::meta('name', 'twitter:title', $title),
            self::meta('name', 'twitter:description', $description),
            self::meta('name', 'twitter:image', $poster),
            self::link('stylesheet', AssetUrl::versioned('/css/reading.css')),
            Element::create('script', <<<'JSON'
            {
              "@context": "https://schema.org",
              "@type": "CoachingService",
              "provider": {
                "@type": "Person",
                "name": "Joshua Bruce",
                "url": "https://itsjoshbruce.com"
              },
              "areaServed": "Remote"
            }
            JSON)->props('type application/ld+json')
        ];

        return (string) Document::create($pageTitle, 'en-US')->head(...$head)->body(
            Element::create('a', 'Skip to main content')->props('is skip-link', 'href #main'),
            Element::create(
                'header',
                self::navigation('Menu', 'primary-navigation', $route, [
                    '/' => 'Home',
                    '/coaching-and-mentoring/' => 'Coaching',
                    '/services/' => 'Services',
                    '/resources/' => 'Resources',
                    '/books/' => 'Books'
                ]),
                Element::create('h1', $heading),
                Element::create('a', 'Apps')->props(
                    'href https://apps.mastering-the-mundane.com',
                    'target _blank',
                    'rel noopener noreferrer'
                )
            )->props('is marketing-header'),
            Element::create(
                'main',
                ContentFragment::forRoute($route)
            )->props('id main', 'tabindex -1'),
            Element::create(
                'footer',
                Copyright::create('Mastering the Mundane', '2023')
            )
        );
    }

    /**
     * @param array<string, string> $destinations
     */
    private static function navigation(
        string $label,
        string $id,
        string $route,
        array $destinations
    ): Element {
        $items = [];
        foreach ($destinations as $href => $text) {
            $link = Element::create('a', $text)->props('href ' . $href);
            if ($href === $route) {
                $link = $link->prop('aria-current page');
            }
            $items[] = Element::create('li', $link);
        }
        return Element::create(
            'nav',
            Element::create('button', $label)->props('type button', 'popovertarget ' . $id),
            Element::create('div', Element::create('ul', ...$items))->props(
                'is navigation-panel',
                'id ' . $id,
                'popover auto'
            )
        )->props('is ' . strtolower($label) . '-picker', 'aria-label ' . $label);
    }

    private static function meta(string $attribute, string $name, string $content): Element
    {
        return Element::create('meta')->omitEndTag()->props(
            $attribute . ' ' . $name,
            'content ' . $content
        );
    }

    private static function link(string $rel, string $href): Element
    {
        return Element::create('link')->omitEndTag()->props('rel ' . $rel, 'href ' . $href);
    }
}
