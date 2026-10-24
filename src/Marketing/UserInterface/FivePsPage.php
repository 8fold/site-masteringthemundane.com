<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class FivePsPage
{
    public static function render(): string
    {
        return PageRenderer::render('/resources/the-5-ps/');
    }
}
