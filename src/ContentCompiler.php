<?php

declare(strict_types=1);

namespace MTM;

use Eightfold\Markdown\Markdown;
use MTM\Marketing\UserInterface\NewTabLink;

final class ContentCompiler
{
    public static function compile(string $markdown): string
    {
        // Only trusted, reviewed repository Markdown enters this build-time converter.
        return NewTabLink::decorate(Markdown::create()->withConfig([
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 100
        ])->tables()->descriptionLists()->abbreviations()->externalLinks([
            'internal_hosts' => ['/\Amastering-the-mundane\.com\z/i'],
            'open_in_new_window' => true,
            'noopener' => 'external',
            'noreferrer' => 'external',
            'nofollow' => ''
        ])->convert($markdown));
    }
}
