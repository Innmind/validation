# Constraints

Each `Constraint` have the following methods:

- `->__invoke()`
- `->and()`
- `->guard()`
- `->or()`
- `->xor()`
- `->map()`
- `->asPredicate()`
- `->failWith()`

## `->__invoke()`

This method is the one to apply the validation on an input and will return a [`Validation`](https://innmind.org/Immutable/structures/validation/) monad that will contain either the validated data or the error messages.

Let's take a simple example to check if the input of a method is a `string`:

```php
use Innmind\Validation\Is;

function(mixed $input): string {
    $validate = Is::string();

    return $validate($input)->match(
        static fn(string $value) => $value,
        static fn() => throw new \RuntimeException('Input is not a string');
    );
}
```

??? info
    Note that we use the style `$validate($input)` and not `$validate->__invoke($input)`. This style allows to treat the constraints as if it were native functions.

## `->and()`

This method allows to apply extra validation on an input.

Let's take the example of making sure a `string` is shorter than `255` characters:

```php hl_lines="3-4 6 9-14"
use Innmind\Validation\{
    Is,
    Constraint,
    Failure,
};
use Innmind\Immutable\Validation;

function(mixed $input): string {
    $validate = Is::string()->and(Constraint::of(
        static fn(string $value) => match (true) {
            \strlen($value) < 255 => Validation::success($value),
            default => Validation::fail(Failure::of('String is too long')),
        },
    ));

    return $validate($input)->match(
        static fn(string $value) => $value,
        static fn() => throw new \RuntimeException('Input is not valid');
    );
}
```

## `->guard()`

This is like [`->and()`](#-and) except that the failures of the constraint passed as argument won't be recovered by a call to [`->xor()`](#-xor).

## `->or()`

This method allows to have an alternate validation in case the first one fails. This is useful for unions types.

Let's take the example where the input needs to be a `string` or an `int`:

```php hl_lines="3-4 7"
use Innmind\Validation\Is;

function(mixed $input): string|int {
    $validate = Is::string()->or(Is::int());

    return return $validate($input)->match(
        static fn(string|int $value) => $value,
        static fn() => throw new \RuntimeException('Input is not valid'),
    );
}
```

## `->xor()`

```php
use Innmind\Validation\Is;

$validate = Is::string()
    ->guard(Is::value('foobar'))
    ->xor(Is::int());
$validate($value)->match(
    static fn(string|int $value) => $value,
    static fn() => throw new \RuntimeException('Input is not valid'),
);
```

Unlike `->or()`, if `#!php $value` is any string other than `foobar` this will raise an exception. This is because the failure due to `#!php Is::value('foobar')` is _guarded_.

This is the only combination preventing a failure from being recovered. Replacing `->guard()` by `->and()` or `->xor()` by `->or()` will work the same way as `->and()->or()`.

## `->map()`

This method allows to transform the validated value to anything you want.

Let's take the example where a need a `string` but want to output a [`Str`](https://innmind.org/Immutable/structures/str/) for ease of use afterwards:

```php hl_lines="2 4-7 10"
use Innmind\Validation\Is;
use Innmind\Immutable\Str;

function(mixed $input): Str {
    $validate = Is::string()->map(
        static fn(string $value) => Str::of($value),
    );

    return return $validate($input)->match(
        static fn(Str $value) => $value,
        static fn() => throw new \RuntimeException('Input is not valid');
    );
}
```

## `->asPredicate()`

A `Predicate` acts as a _function_ that returns a `bool`.

It's intended to be used with [`Sequence::keep()`](https://innmind.org/Immutable/structures/sequence/#-keep) or [`Set::keep()`](https://innmind.org/Immutable/structures/set/#-keep).

Here's an example to keep all `string`s inside a `Sequence`:

```php
use Innmind\Validation\Is;
use Innmind\Immutable\Sequence;

Sequence::of(1, 'a', null, 'b', new \stdClass, 'c')
    ->keep(Is::string()->asPredicate())
    ->toList(); // returns ['a', 'b', 'c']
```

??? note
    There's no need to apply transformations on your constraints when used as predicates as the outputed value is not used.

## `->failWith()`

This method allows to change the failure message.

```php
use Innmind\Validation\Is;

$password = Is::string()->failWith('The password is required');

$password($someInput);
```

## Handling failures

In the examples above we've thrown exceptions in case of errors but you have access to all the failures messages and where they happened.

```php
use Innmind\Validation\{
    Is,
    Failure,
};
use Innmind\Immutable\Sequence;

$validate = Is::shape('id', Is::int())
    ->with('username', Is::string());

$validate($input)->match(
    static fn(array $valid) => $valid,
    static fn(Sequence $failures) => \var_dump(
        $failures
            ->map(static fn(Failure $failure) => [
                $failure->path()->toString(),
                $failure->message(),
            ])
            ->toList(),
    ),
);
```

In case `$input` is invalid it will print:

=== "Not an `array`"
    ```
    [['$', 'Value is not of type array']]
    ```

=== "`[]`"
    ```
    [
        ['$', 'The key id is missing'],
        ['$', 'The key username is missing'],
    ]
    ```

=== "`['id' => 'wrong', 'username' => false]`"
    ```
    [
        ['id', 'Value is not of type int'],
        ['username', 'Value is not of type string'],
    ]
    ```
