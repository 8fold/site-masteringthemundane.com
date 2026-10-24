<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class CoachingPage
{
    public static function render(): string
    {
        return PageRenderer::render('/coaching-and-mentoring/');
    }
}
