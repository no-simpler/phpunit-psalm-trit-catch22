<?php

declare(strict_types=1);

namespace App\Test;

use App\EnumUsingTrait;
use App\SomeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(EnumUsingTrait::class)]
#[CoversTrait(SomeTrait::class)]
final class EnumCoveredCoversTraitTest extends TestCase
{
    public function testTraitMethod(): void
    {
        self::assertSame('from trait', EnumUsingTrait::FOO->traitMethod());
    }
}
