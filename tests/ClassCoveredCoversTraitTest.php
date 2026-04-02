<?php

declare(strict_types=1);

namespace App\Test;

use App\ClassUsingTrait;
use App\SomeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(ClassUsingTrait::class)]
#[CoversTrait(SomeTrait::class)]
final class ClassCoveredCoversTraitTest extends TestCase
{
    public function testTraitMethod(): void
    {
        $obj = new ClassUsingTrait();
        self::assertSame('from trait', $obj->traitMethod());
    }
}
