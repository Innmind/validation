# Objects

You can check the input is an object like this:

```php
use Innmind\Validation\Constraint;

$validate = Constraint::object();
```

And if you want to make sure it is an instance of some class:

```php
use Innmind\Validation\Instance;

$validate = Instance::of(SomeClass::class);
```
