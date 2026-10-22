<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class BooksPage
{
    public static function render(): string
    {
        return PageMetadata::render('/books/');
    }
}
