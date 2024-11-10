# Primitives

=== "`string`"
    ```php
    use Innmind\Validation\Is;

    $validate = Is::string();
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
$validate = Is::string()->withFailure('Some error message');
```

## Lists

This constraint validates the input is an `array` and all values are consecutive (1).
{.annotate}

1. No index value specified, be it `int`s or `string`s.

```php
use Innmind\Validation\Is;

$validate = Is::list();
```

You can also validate that each value in the list is of a given type. Here's how to validate a list os `string`s:

```php
use Innmind\Validation\Is;

$validate = Is::list(Is::string());
```
