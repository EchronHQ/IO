<?php
declare(strict_types = 1);

namespace Echron\IO\Data;

use Echron\Tools\TypedEnum;

class FileType extends TypedEnum
{
    public static function Unknown():FileType
    {
        return self::_create('unknown');
    }

    public static function File():FileType
    {
        return self::_create('file');
    }

    public static function Dir():FileType
    {
        return self::_create('dir');
    }
    public static function Link():FileType
    {
        return self::_create('link');
    }
}
