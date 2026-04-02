<?php

declare(strict_types=1);

namespace App;

trait SomeTrait
{
    public function traitMethod(): string
    {
        return 'from trait';
    }
}
