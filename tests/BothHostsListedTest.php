<?php

declare(strict_types=1);

namespace App\Test;

use App\ClassUsingTrait;
use App\EnumUsingTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(ClassUsingTrait::class)]
#[UsesClass(EnumUsingTrait::class)]
final class BothHostsListedTest extends TestCase
{
    public function testTraitMethod(): void
    {
        $obj = new ClassUsingTrait();
        self::assertSame('from trait', $obj->traitMethod());

        self::assertSame('from trait', EnumUsingTrait::FOO->traitMethod());
    }
}
