<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class FivePsPage
{
    public static function render(): string
    {
        return ReadingPage::render(
            '/resources/the-5-ps/',
            'The 5 Ps exercise by Josh Bruce',
            'The 5 Ps Framework: Design Your Life (Passions, Pensives, More)'
                . ' | Tools and Resources | Mastering the Mundane',
            'A life design framework to clarify your passions, prohibitions, and career path. '
                . 'Explore the 5 Ps exercise used in team culture and personal coaching.'
        );
    }
}
