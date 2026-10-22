<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class ActionLoopPage
{
    public static function render(): string
    {
        return PageMetadata::render('/resources/the-action-loop/');
    }
}
