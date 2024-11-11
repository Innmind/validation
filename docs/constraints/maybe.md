# Maybe monad

If a previous contraint outputs a [`Maybe`](https://innmind.org/Immutable/structures/maybe/) and you want to access the inner value you can do:

```php
use Innmind\Validation\Is;
use Innmind\Immutable\Maybe;

$validate = Is::int()
    ->or(Is::null())
    ->map(Maybe::of(...))
    ->and(Is::just());
```

In this example the input can be an `int` or `null` but it will fail the validation in case the value is `null` because `Maybe::of(...)` will move the `null` as a `Nothing` and we say we want a `Just`.
