# Validation

[![Build Status](https://github.com/innmind/validation/workflows/CI/badge.svg?branch=main)](https://github.com/innmind/validation/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/validation/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/validation)
[![Type Coverage](https://shepherd.dev/github/innmind/validation/coverage.svg)](https://shepherd.dev/github/innmind/validation)

This package is a monadic approach to data validation to easily compose validations rules.

## Installation

```sh
composer require innmind/validation
```

## Usage

```php
use Innmind\Validation\{
    Shape,
    Is,
    Each,
    Failure,
};
use Innmind\Immutable\Sequence;

$valid = [
    'id' => 42,
    'username' => 'jdoe',
    'addresses' => [
        'address 1',
        'address 2',
    ],
    'submit' => true,
];
$invalid = [
    'id' => '42',
    'addresses' => [
        'address 1',
        null,
    ],
    'submit' => true,
];

$validate = Shape::of('id', Is::int())
    ->with('username', Is::string())
    ->with(
        'addresses',
        Is::list(
            Is::string()->map(
                static fn(string $address) => new YourModel($address),
            )
        )
    );
$result = $validate($valid)->match(
    static fn(array $value) => $value,
    static fn() => throw new \RuntimeException('invalid data'),
);
// Here $result looks like:
// [
//      'id' => 42
//      'username' => 'jdoe',
//      'addresses' [
//          new YourModel('address 1'),
//          new YourModel('address 2'),
//      ],
// ]
$errors = $validate($invalid)->match(
    static fn() => null,
    static fn(Sequence $failures) => $failures
        ->map(static fn(Failure $failure) => [
            $failure->path()->toString(),
            $failure->message(),
        ])
        ->toList(),
);
// Here $errors looks like:
// [
//      ['id', 'Value is not of type int'],
//      ['$', 'The key username is mission'],
//      ['addresses', 'Value is not of type string']
// ]
```
