<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class TimeBookPage
{
    public static function render(): string
    {
        return PageRenderer::render('/books/time-mastering-the-mundane/');
    }
}
