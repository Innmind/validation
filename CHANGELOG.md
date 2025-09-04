# Changelog

## [Unreleased]

### Added

- `Innmind\Validation\Constraint::guard()`
- `Innmind\Validation\Constraint::xor()`

### Changed

- Requires `innmind/immutable:~5.19`

## 2.1.0 - 2025-08-30

### Added

- `Innmind\Validation\Constraint::flatMap()`

### Changed

- `Innmind\Validation\Failure::of()` is now longer flagged as internal

## 2.0.0 - 2025-04-12

### Added

- `Innmind\Validation\Constraint::object()`
- `Innmind\Validation\Constraint::failWith()`
- `Innmind\Validation\Constraint::string()->nonEmpty()`
- `Innmind\Validation\Constraint::int()->positive()`
- `Innmind\Validation\Constraint::int()->negative()`
- `Innmind\Validation\Constraint::int()->range()`
- `Innmind\Validation\Constraint\Provider`

### Changed

- `Innmind\Validation\Constraint` is now a `final` class
- `Innmind\Validation\Failure::of()` is now `internal`
- `Innmind\Validation\Failure::under()` is now `internal`
- `Innmind\Validation\KeyPath::root()` is now `internal`
- `Innmind\Validation\KeyPath::under()` is now `internal`
- `Innmind\Validation\Predicate` is now `internal`

### Removed

- `Innmind\Validation\Each`, you must use `Is::list()` instead
- `Innmind\Validation\AndConstraint`, you must use `Constraint::and()` instead
- `Innmind\Validation\OrConstraint`, you must use `Constraint::or()` instead
- `Innmind\Validation\Map`, you must use `Constraint::map()` instead

## 1.9.0 - 2025-02-09

### Added

- Support for `innmind/time-continuum` `4`

## 1.8.0 - 2025-02-09

### Added

- `Is::value()` to allow to defined discriminators

### Fixed

- Support for PHP `8.4`

## 1.7.0 - 2024-11-11

### Changed

- `Constraint::map()` callable no longer needs to be pure

## 1.6.1 - 2024-11-11

### Fixed

- Psalm losing the types of contraints when composing them via `and`, `or`, `map` and `asPredicate`

## 1.6.0 - 2024-11-11

### Added

- `Shape::rename()` to rename a key in the output array
- `Shape::default()` to specify a default value when an optional key is not set
- `Is::just()`

## 1.5.0 - 2024-11-10

### Added

- `Is::associativeArray()`
- `Has::key()->withFailure()` to change the failure message
- `Is::string|int|float|array|bool|null()->withFailure()` to change the failure message
- `PointInTime::ofFormat()->withFailure()` to change the failure message
- `Is::shape()->withKeyFailure()` to change the failure message for when a key doesn't exist

## 1.4.0 - 2024-03-24

### Added

- `Is::list()->and(Each::of(Constraint))` has been shortened to `Is::list(Constraint)`

### Changed

- `Is::array()->and(Is::list())` has been shortened to `Is::list()`
- `Is::array()->and(Shape::of(...$args))` has been shortened to `Shape::of(...$args)`

## 1.3.0 - 2024-03-05

### Added

- `Innmind\Validation\Is::shape()` as a shortcut to `Is::array()->and(Shape::of())`

### Fixed

- `OrConstraint::and()` was applying an _or_ constraint

## 1.2.0 - 2024-03-05

### Added

- You can now do `Shape::of(...)->optional('key', $constraint)` instead of `Shape::of(...)->with('key', $constraint)->optional('key')` thus avoiding to repeat the key name

## 1.1.1 - 2024-02-24

### Fixed

- `Innmind\Validation\Each` now returns the validated data instead of the original content

## 1.1.0 - 2023-11-22

### Added

- `Innmind\Validation\Shape::optional()`
