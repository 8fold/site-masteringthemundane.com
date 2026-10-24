<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class MotivatorsPage
{
    public static function render(): string
    {
        return PageRenderer::render('/resources/motivators/');
    }
}
