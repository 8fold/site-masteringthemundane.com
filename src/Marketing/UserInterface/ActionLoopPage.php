<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class ActionLoopPage
{
    public static function render(): string
    {
        return ReadingPage::render(
            '/articles/the-action-loop/',
            'Action loop',
            'The Action Loop: Work Sustainably and Get Things Done | Articles and Thoughts'
                . ' | Mastering the Mundane',
            'Master the 5-phase action loop to sustain energy, prevent burnout, '
                . 'and maintain peak productivity. Learn the framework for intentional, '
                . 'balanced progress.'
        );
    }
}
