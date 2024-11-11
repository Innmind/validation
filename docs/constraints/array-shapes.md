# Array shape

Unlike an [associative array](associative-arrays.md), a shape is an array where the keys are known in advance.

```php
use Innmind\Validation\Is;

$validate = Is::shape('id', Is::int())
    ->with('username', Is::string());
```

This contraint will accept any array that looks like:

```php
[
    'id' => 42, #(1)
    'username' => 'any string',
]
```

1. or any `int`

If the input data contains more keys than the expected ones, the output will only contains the keys you specified. This is to prevent any unwanted data injection in your code.

## Optional keys

Here's an example of a login payload where the "Keep me logged in" is optional:

```php
use Innmind\Validation\Is;

$validate = Is::shape('username', Is::string())
    ->with('password', Is::string())
    ->optional('keep-logged-in', Is::string());
```

The optional key will only be present in the output value if it was set in the input.

??? tip
    If you want to build a shape with a single optional key you can do `#!php Is::shape('key', Is::string())->optional('key')`.

If you don't want to handle the possible absence of the key in the output array you can specify a default value:

```php hl_lines="6"
use Innmind\Validation\Is;

$validate = Is::shape('username', Is::string())
    ->with('password', Is::string())
    ->optional('keep-logged-in', Is::string())
    ->default('keep-logged-in', 'false');
```

## Rename a key

This is useful when the output value no longer matches the input key name.

For example you have a shape containing a list of integers but you want the highest one:

```php
use Innmind\Validation\Is;

$validate = Is::shape(
    'versions',
    Is::list(Is::int())
        ->map(\max(...)),
)->rename('versions', 'highest');
```

Now the output type is `array{highest: int}`.
