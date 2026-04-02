<?php

declare(strict_types=1);

namespace App\Test;

use App\ClassUsingTrait;
use App\SomeTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversTrait(SomeTrait::class)]
final class TraitDirectCoversTest extends TestCase
{
    public function testTraitMethod(): void
    {
        $obj = new ClassUsingTrait();
        self::assertSame('from trait', $obj->traitMethod());
    }
}
