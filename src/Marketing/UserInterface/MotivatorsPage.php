<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class MotivatorsPage
{
    public static function render(): string
    {
        return ReadingPage::render(
            '/resources/motivators/',
            'The Motivators exercise by Josh Bruce',
            'The Motivators Exercise: Clarify Your Core Drivers | Tools and Resources'
                . ' | Mastering the Mundane',
            'Discover what truly motivates you. The Motivators exercise clarifies your core '
                . 'drivers across 8 dimensions—for self-discovery, team building, '
                . 'and intentional life design.'
        );
    }
}
