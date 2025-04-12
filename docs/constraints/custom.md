# Custom

At some point you'll want to use your own constraints for a custom business logic. You can do:

```php
use Innmind\Validation\{
    Constraint,
    Failure,
};
use Innmind\Immutable\Validation;

$validate = Constraint::of(static function(mixed $input) {
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
    Constraint,
    Failure,
};
use Innmind\Immutable\Validation;

$validate = Is::string()->and(Constraint::of(static function(string $input) {
    if (/* your validation here */) {
        return Validation::success($input);
    }

    return Validation::fail(Failure::of('Your error message'));
}));
```

You don't need to write all the validations yourself in the `callable`.
