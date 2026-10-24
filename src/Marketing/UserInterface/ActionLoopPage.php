<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class ActionLoopPage
{
    public static function render(): string
    {
        return PageRenderer::render('/resources/the-action-loop/');
    }
}
