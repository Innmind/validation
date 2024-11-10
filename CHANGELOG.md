# Changelog

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
