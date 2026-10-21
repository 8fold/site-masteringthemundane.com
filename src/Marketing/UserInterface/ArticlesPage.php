<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class ArticlesPage
{
    public static function render(): string
    {
        return ReadingPage::render(
            '/articles/',
            'Articles',
            'Articles | Mastering the Mundane',
            'Articles on working sustainably and getting things done.'
        );
    }
}
