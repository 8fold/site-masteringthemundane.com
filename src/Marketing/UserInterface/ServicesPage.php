<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class ServicesPage
{
    public static function render(): string
    {
        $route = '/services/';
        $meta = PageMetadata::forRoute($route);

        return ReadingPage::render(
            $route,
            $meta['titles'],
            $meta['description'],
            ContentFragment::forRoute($route)
        );
    }
}
