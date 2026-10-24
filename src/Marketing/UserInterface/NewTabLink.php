<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use Dom\HTMLDocument;
use Dom\Element;
use Dom\Node;
use Dom\Text;
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
        $tailTemplate = $document->createElement('span');
        $tailTemplate->setAttribute('class', 'new-tab-tail');
        $iconTemplate = $document->createElementNS('http://www.w3.org/2000/svg', 'svg');
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
            $iconTemplate->setAttribute($name, $value);
        }
        $path = $document->createElementNS('http://www.w3.org/2000/svg', 'path');
        $path->setAttribute('d', 'M9.5 2.5h4v4m0-4-7 7M7 3H3v10h10V9');
        $iconTemplate->append($path);
        $announcementTemplate = $document->createElement('span');
        $announcementTemplate->setAttribute('class', 'new-tab-announcement');
        $announcementTemplate->textContent = ' (opens in a new tab)';

        foreach ($document->querySelectorAll('a[target="_blank"]') as $anchor) {
            if ($anchor->querySelector('span[class="new-tab-tail"]') !== null) {
                continue;
            }
            $text = self::lastNonWhitespaceText($anchor);
            $tail = self::cloneElement($tailTemplate);
            if ($text instanceof Text && preg_match('/(\S+)(\s*)$/u', $text->data, $match)) {
                $prefix = substr($text->data, 0, -strlen($match[0]));
                $text->before($document->createTextNode($prefix), $tail);
                $tail->append($document->createTextNode($match[1]));
                $text->data = $match[2];
            } else {
                $anchor->append($tail);
            }
            $tail->append(self::cloneElement($iconTemplate));
            $announcement = self::cloneElement($announcementTemplate);
            $anchor->append($announcement);
        }
        $body = $document->body;
        if ($body === null) {
            throw new RuntimeException('New-tab link decoration requires an HTML body.');
        }
        return $body->innerHTML;
    }

    private static function cloneElement(Element $element): Element
    {
        $clone = $element->cloneNode(true);
        if (!$clone instanceof Element) {
            throw new RuntimeException('New-tab link decoration requires an element.');
        }
        return $clone;
    }

    private static function lastNonWhitespaceText(Node $node): ?Text
    {
        $lastText = null;
        foreach ($node->childNodes as $child) {
            if ($child instanceof Text) {
                if (trim($child->data, " \t\n\r") !== '') {
                    $lastText = $child;
                }
                continue;
            }
            $descendantText = self::lastNonWhitespaceText($child);
            if ($descendantText instanceof Text) {
                $lastText = $descendantText;
            }
        }
        return $lastText;
    }
}
