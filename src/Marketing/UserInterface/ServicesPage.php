<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

final class ServicesPage
{
    public static function render(): string
    {
        return ReadingPage::render(
            '/services/',
            'Services',
            'Services | Mastering the Mundane',
            'Josh Bruce has spent his life making peace with time, space, and money '
                . 'while also helping others do the same. Heʼs worked with individuals, '
                . 'The United States Federal Government Agencies, micro-businesses, Fortune 500 '
                . 'companies in the for-profit and not-for-profit categories.'
        );
    }
}
