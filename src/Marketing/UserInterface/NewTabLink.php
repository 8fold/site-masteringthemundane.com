<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use Dom\HTMLDocument;
use Dom\Element;
use Dom\Text;
use Dom\XPath;
use RuntimeException;

final class NewTabLink
{
    public static function decorate(string $html): string
    {
        $document = HTMLDocument::createFromString(
            '<!doctype html><html><head><meta charset="utf-8"></head><body>'
                . $html . '</body></html>',
            LIBXML_NOERROR
        );
        $xpath = new XPath($document);
        $xpath->registerNamespace('h', 'http://www.w3.org/1999/xhtml');
        foreach ($xpath->query('//h:a[@target="_blank"]') as $anchor) {
            if (!$anchor instanceof Element) {
                throw new RuntimeException('New-tab link decoration requires an element.');
            }
            if ($xpath->query('.//h:span[@class="new-tab-tail"]', $anchor)->length > 0) {
                continue;
            }
            $texts = $xpath->query('(.//text()[normalize-space(.) != ""])[last()]', $anchor);
            $text = $texts->item(0);
            $tail = $document->createElement('span');
            $tail->setAttribute('class', 'new-tab-tail');
            if ($text instanceof Text && preg_match('/(\S+)(\s*)$/u', $text->data, $match)) {
                $prefix = substr($text->data, 0, -strlen($match[0]));
                $text->before($document->createTextNode($prefix), $tail);
                $tail->append($document->createTextNode($match[1]));
                $text->data = $match[2];
            } else {
                $anchor->append($tail);
            }
            $icon = $document->createElementNS('http://www.w3.org/2000/svg', 'svg');
            foreach (
                [
                'class' => 'new-tab-icon',
                'viewBox' => '0 0 16 16',
                'fill' => 'none',
                'stroke' => 'currentColor',
                'stroke-width' => '1.5',
                'stroke-linecap' => 'round',
                'stroke-linejoin' => 'round',
                'aria-hidden' => 'true',
                'focusable' => 'false'
                ] as $name => $value
            ) {
                $icon->setAttribute($name, $value);
            }
            $path = $document->createElementNS('http://www.w3.org/2000/svg', 'path');
            $path->setAttribute('d', 'M9.5 2.5h4v4m0-4-7 7M7 3H3v10h10V9');
            $icon->append($path);
            $tail->append($icon);
            $announcement = $document->createElement('span');
            $announcement->setAttribute('class', 'new-tab-announcement');
            $announcement->textContent = ' (opens in a new tab)';
            $anchor->append($announcement);
        }
        $result = '';
        $body = $document->body;
        if ($body === null) {
            throw new RuntimeException('New-tab link decoration requires an HTML body.');
        }
        foreach ($body->childNodes as $child) {
            $result .= $document->saveHtml($child);
        }
        return $result;
    }
}
