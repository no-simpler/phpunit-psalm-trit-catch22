<?php

declare(strict_types=1);

namespace App\Test;

use App\ClassUsingTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(ClassUsingTrait::class)]
final class ClassCoveredTest extends TestCase
{
    public function testTraitMethod(): void
    {
        $obj = new ClassUsingTrait();
        self::assertSame('from trait', $obj->traitMethod());
    }
}
