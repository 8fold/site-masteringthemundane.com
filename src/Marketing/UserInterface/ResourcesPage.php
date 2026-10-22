<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class ResourcesPage
{
    public static function render(): string
    {
        return ReadingPage::render(
            '/resources/',
            'Resources',
            'Resources | Mastering the Mundane',
            'Models and exercises for working sustainably and getting things done.'
        );
    }
}
