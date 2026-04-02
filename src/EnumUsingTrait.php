<?php

declare(strict_types=1);

namespace App;

enum EnumUsingTrait: string
{
    use SomeTrait;

    case FOO = 'foo';
}
