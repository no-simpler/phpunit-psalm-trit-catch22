# PHPUnit / Psalm catch-22: enum trait coverage metadata

When a PHP enum uses a trait, PHPUnit 12's strict coverage metadata and Psalm 6's
type checking create a situation where no single set of attributes satisfies both tools.

## Versions tested

- PHP 8.4.19
- PHPUnit 12.5.15
- Psalm 6.16.1
- psalm/plugin-phpunit 0.19.6
- PCOV 1.0.12

## Reproduce

All files are already in this directory. From a clean clone:

```sh
docker compose up -d --build
docker compose exec app composer install
docker compose exec app vendor/bin/phpunit --testdox
docker compose exec app vendor/bin/psalm --no-cache
docker compose down
```

## Project structure

```
Dockerfile              # PHP 8.4 + pcov + Composer
compose.yaml
composer.json           # phpunit ^12, psalm ^6, psalm/plugin-phpunit ^0.19
phpunit.dist.xml        # requireCoverageMetadata + beStrictAboutCoverageMetadata + coverage report
psalm.xml               # errorLevel 1, phpunit plugin

src/
  SomeTrait.php         # trait SomeTrait { traitMethod(): string }
  ClassUsingTrait.php   # final class ClassUsingTrait { use SomeTrait; }
  EnumUsingTrait.php    # enum EnumUsingTrait: string { use SomeTrait; case FOO = 'foo'; }

tests/                  # 8 test classes, one per matrix row (see below)
```

## Test matrix

Each test is `#[Small]` and calls `traitMethod()` on its subject.

| # | Test class                    | Attributes                                               | PHPUnit | Psalm              |
|---|-------------------------------|----------------------------------------------------------|---------|--------------------|
| 1 | `ClassCoveredTest`            | `CoversClass(ClassUsingTrait)`                           | Pass    | Pass               |
| 2 | `ClassCoveredUsesTraitTest`   | `CoversClass(ClassUsingTrait)` + `UsesTrait(SomeTrait)`  | Pass    | **InvalidArgument** |
| 3 | `ClassCoveredCoversTraitTest` | `CoversClass(ClassUsingTrait)` + `CoversTrait(SomeTrait)`| Pass    | **InvalidArgument** |
| 4 | `EnumCoveredTest`             | `CoversClass(EnumUsingTrait)`                            | **Risky** | Pass             |
| 5 | `EnumCoveredUsesTraitTest`    | `CoversClass(EnumUsingTrait)` + `UsesTrait(SomeTrait)`   | Pass    | **InvalidArgument** |
| 6 | `EnumCoveredCoversTraitTest`  | `CoversClass(EnumUsingTrait)` + `CoversTrait(SomeTrait)` | Pass    | **InvalidArgument** |
| 7 | `TraitDirectCoversTest`       | `CoversTrait(SomeTrait)` only                            | Pass    | **InvalidArgument** |
| 8 | `BothHostsListedTest`         | `CoversClass(ClassUsingTrait)` + `UsesClass(EnumUsingTrait)` | Pass | Pass              |

## Findings

### 1. `#[CoversClass]` handles traits differently for classes vs enums

When a **class** uses a trait, `#[CoversClass(TheClass::class)]` transitively whitelists
the trait's code for coverage purposes (row 1: PHPUnit passes).

When an **enum** uses a trait, the same `#[CoversClass(TheEnum::class)]` does **not**
transitively whitelist the trait (row 4: PHPUnit reports a risky test).

### 2. Psalm rejects every `#[UsesTrait]` and `#[CoversTrait]` attribute

Psalm emits `InvalidArgument` for `#[UsesTrait(SomeTrait::class)]` and
`#[CoversTrait(SomeTrait::class)]`, claiming the argument is not a `trait-string`.
This affects every row that uses either attribute (rows 2, 3, 5, 6, 7).

### 3. The catch-22

For an enum that uses a trait, you must add `#[UsesTrait(SomeTrait::class)]` or
`#[CoversTrait(SomeTrait::class)]` to satisfy PHPUnit (rows 5 or 6). But Psalm rejects
both of these attributes. There is no attribute combination that satisfies both tools:

- Row 4: Psalm passes, PHPUnit fails (risky).
- Rows 5, 6: PHPUnit passes, Psalm fails (InvalidArgument).

For classes this conflict is latent: `#[CoversClass]` alone satisfies PHPUnit (row 1),
so you never need the attributes that Psalm rejects.

### 4. The `trait-string` issue is a Psalm bug, not scoped to enums

Psalm rejects `UsesTrait`/`CoversTrait` even when used with a class host (rows 2, 3) or
standalone (row 7). The `SomeTrait::class` expression evaluates to the correct
`class-string`, but Psalm's `trait-string` type is not satisfied by `::class` on a trait.
The catch-22 only *surfaces* with enums because that's when PHPUnit forces you to use
these attributes.

## Why this matters

Using PHPUnit 12 with strict coverage metadata (`requireCoverageMetadata`,
`beStrictAboutCoverageMetadata`) alongside Psalm at error level 1 is not an unusual or
unreasonable configuration for a PHP project. Both tools are industry-standard, their
strictest modes are designed to be used together, and running both in CI is common
practice.

The current superposition of two technically unrelated behaviors -- PHPUnit not
transitively whitelisting traits on enums, and Psalm not recognizing `trait-string` from
`::class` expressions -- creates a situation where the only viable workaround is to
suppress the Psalm error for the affected test class:

```php
/** @psalm-suppress InvalidArgument */
#[UsesTrait(SomeTrait::class)]
```

This is undesirable because `@psalm-suppress InvalidArgument` on a class is a broad
suppression that may mask other, legitimate type errors within the same class. The
suppression cannot be scoped more narrowly: it must cover the attribute, which is on the
class, so the entire class is affected.

The cleaner fix would come from either side:
- **PHPUnit** could transitively whitelist traits for enums the same way it does for
  classes, eliminating the need for `#[UsesTrait]` on enum tests entirely.
- **Psalm** could recognize that `SomeTrait::class` satisfies `trait-string`, eliminating
  the false positive on `#[UsesTrait]` and `#[CoversTrait]`.

Either fix alone would break the catch-22.
