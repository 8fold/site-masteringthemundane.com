<?php

declare(strict_types=1);

namespace MTM\Shared\UserInterface;

use Stringable;
use Eightfold\HtmlBuilder\Document;
use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\Components\Copyright;
use Eightfold\HtmlBuilder\Components\Favicons;
use Eightfold\HtmlBuilder\Components\PageTitle;
use MTM\Marketing\UserInterface\AssetUrl;
use MTM\Marketing\UserInterface\NewTabLink;
use MTM\Marketing\StructuredData;

final class DocumentBase implements Stringable
{
    /** @var list<string|Stringable> */
    private array $headJs = [];

    /** @var list<string> */
    private array $headCss = [];

    /** @var list<string|Stringable> */
    private array $introContent = [];

    /** @var list<string|Stringable> */
    private array $content = [];

    /**
     * @param non-empty-list<string> $titles
     */
    public static function create(string $route, array $titles, string $description): self
    {
        return new self($route, $titles, $description);
    }

    /**
     * @param non-empty-list<string> $titles
     */
    private function __construct(
        private readonly string $route,
        private readonly array $titles,
        private readonly string $description
    ) {
    }

    /** @param string|Stringable $js Script components or inline script elements. */
    public function headJs(string|Stringable ...$js): self
    {
        $this->headJs = array_values($js);
        return $this;
    }

    /** @param string $cssFiles Local stylesheet paths under the public webroot. */
    public function headCss(string ...$cssFiles): self
    {
        $this->headCss = array_values($cssFiles);
        return $this;
    }

    /** @param string|Stringable $content Trusted, already-rendered HTML. */
    public function introContent(string|Stringable ...$content): self
    {
        $this->introContent = array_values($content);
        return $this;
    }

    /** @param string|Stringable $content Trusted, already-rendered HTML. */
    public function content(string|Stringable ...$content): self
    {
        $this->content = array_values($content);
        return $this;
    }

    public function __toString(): string
    {
        $titles = array_map(self::escape(...), $this->titles);
        $description = self::escape($this->description);
        $heading = $titles[0];
        $title = implode(' | ', $titles);
        $domain = 'mastering-the-mundane.com';
        $url = 'https://' . $domain;
        $poster = $url . '/assets/media/poster.png';
        $keywords = 'Time Management, Self Management, Life Management, Life Coaching, '
            . 'Productivity Coaching, Financial Coaching, Professional Organization, '
            . 'bookkeeping, accoutability partner, digital organization, onsite, on-site, '
            . 'remote, hybrid, josh, bruce, josh bruce';

        $head = [
            self::meta('name', 'viewport', 'width=device-width,initial-scale=1'),
            Favicons::create('/favicons', '#ffffff')
                ->withMetro('#ffffff')
                ->withSafariThemeColor('#000000'),
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
            self::link('stylesheet', AssetUrl::versioned('/assets/css/reading.min.css'))
                ->prop('blocking render')
        ];
        foreach ($this->headCss as $path) {
            $head[] = self::link('stylesheet', AssetUrl::versioned($path))
                ->prop('blocking render');
        }
        $head = [
            ...$head,
            ...$this->headJs,
            Element::script(StructuredData::forRoute($this->route))
                ->props('type application/ld+json')
        ];

        $introContent = array_map('strval', $this->introContent);
        $content = array_map('strval', $this->content);

        return (string) Document::create(PageTitle::create($titles), 'en-US')->head(...$head)->body(
            Element::a('Skip to main content')->props('is skip-link', 'href #main'),
            Element::header(
                self::navigation('Menu', 'primary-navigation', $this->route, [
                    '/' => 'Home',
                    '/coaching-and-mentoring/' => 'Coaching',
                    '/services/' => 'Services',
                    '/resources/' => 'Resources',
                    '/books/' => 'Books'
                ]),
                Element::h1($heading),
                NewTabLink::decorate((string) Element::a('Apps')->props(
                    'href https://apps.mastering-the-mundane.com',
                    'target _blank',
                    'rel noopener noreferrer'
                ))
            )->props('is nav-header'),
            Element::main(...$introContent, ...$content)->props('id main', 'tabindex -1'),
            Element::footer(
                Copyright::create('Mastering the Mundane', '2023')
            )
        );
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
            $link = Element::a($text)->props('href ' . $href);
            if ($href === $route) {
                $link = $link->prop('aria-current page');
            }
            $items[] = Element::li($link);
        }
        return Element::nav(
            Element::button($label)->props('type button', 'popovertarget ' . $id),
            Element::div(Element::ul(...$items))->props(
                'is nav-panel',
                'id ' . $id,
                'popover auto'
            )
        )->props('is nav-primary', 'aria-label ' . $label);
    }

    private static function meta(string $attribute, string $name, string $content): Element
    {
        return Element::meta()->omitEndTag()->props(
            $attribute . ' ' . $name,
            'content ' . $content
        );
    }

    private static function link(string $rel, string $href): Element
    {
        return Element::link()->omitEndTag()->props('rel ' . $rel, 'href ' . $href);
    }
}
