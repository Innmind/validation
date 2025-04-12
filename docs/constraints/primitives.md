# Primitives

=== "`string`"
    ```php
    use Innmind\Validation\Is;

    $validate = Is::string();
    ```

=== "`non-empty-string`"
    ```php
    use Innmind\Validation\Is;

    $validate = Is::string()->nonEmpty();
    ```

=== "`int`"
    ```php
    use Innmind\Validation\Is;

    $validate = Is::int();
    ```

=== "`float`"
    ```php
    use Innmind\Validation\Is;

    $validate = Is::float();
    ```

=== "`array`"
    ```php
    use Innmind\Validation\Is;

    $validate = Is::array();
    ```

=== "`bool`"
    ```php
    use Innmind\Validation\Is;

    $validate = Is::bool();
    ```

=== "`null`"
    ```php
    use Innmind\Validation\Is;

    $validate = Is::null();
    ```

By default the error message will be `Value is not of type {primitive}`. You can change it via:

```php
$validate = Is::string()->failWith('Some error message');
```

## Lists

This constraint validates the input is an `array` and all values are consecutive (1).
{.annotate}

1. No index value specified, be it `int`s or `string`s.

```php
use Innmind\Validation\Is;

$validate = Is::list();
```

You can also validate that each value in the list is of a given type. Here's how to validate a list of `string`s:

```php
use Innmind\Validation\Is;

$validate = Is::list(Is::string());
```

## Specified value

This constraint makes sure the the input value is the expected one.

```php
use Innmind\Validation\Is;

$validate = Is::value(42);
```

If you call the constraint with any other value than `42`, the validation will fail. Of course you can specify any value of any type you wish.

??? tip
    This is especially useful to define discriminators when the input can be multiple [shapes](array-shapes.md) that are defined by a key.

    ```php
    use Innmind\Validation\Is;

    $shapeA = Is::shape('discriminator', Is::value('a'))
        ->with('some-key', Is::string());
    $shapeB = Is::shape('discriminator', Is::value('b'))
        ->with('other-key', Is::int());

    $validate = $shapeA->or($shapeB);
    ```

    If you can `$validate` with one of the following values it will succeed:

    === "A"
        ```php
        [
            'discriminator' => 'a',
            'some-key' => 'foo',
        ]
        ```

    === "B"
        ```php
        [
            'discriminator' => 'b',
            'other-key' => 42,
        ]
        ```

    Otherwise it will fail for any other array shape.
