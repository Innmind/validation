# Changelog

## [Unreleased]

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
