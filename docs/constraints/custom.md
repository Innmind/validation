# Custom

At some point you'll want to use your own constraints for a custom business logic. Instead of implementing the whole `Constraint` interface you can do:

```php
use Innmind\Validation\{
    Of,
    Failure,
};
use Innmind\Immutable\Validation;

$validate = Of::callable(static function(mixed $input) {
    if (/* your validation here */) {
        return Validation::success($input);
    }

    return Validation::fail(Failure::of('Your error message'));
});
```

And you can still compose it with any other constraint.

For example if you know the input has to be a `string` you can do:

```php hl_lines="7"
use Innmind\Validation\{
    Of,
    Failure,
};
use Innmind\Immutable\Validation;

$validate = Is::string()->and(Of::callable(static function(string $input) {
    if (/* your validation here */) {
        return Validation::success($input);
    }

    return Validation::fail(Failure::of('Your error message'));
}));
```

You don't need to write all the validations yourself in the `callable`.
