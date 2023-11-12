<?php

declare(strict_types=1);

namespace Echron\IO\Data;

enum FileType: string
{
    case Unknown = 'unknown';
    case File = 'file';
    case Dir = 'dir';
    case Link = 'link';
}
