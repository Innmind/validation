# Dates

This will transform `string`s into `PointInTime`s from the [`innmind/time-continuum` package](https://github.com/Innmind/TimeContinuum/).

```php
use Innmind\Validation\Constraint;
use Innmind\TimeContinuum\{
    Clock,
    Format,
};

$validate = Constraint::pointInTime(Clock::live())->format(
    Format::iso8601(),
);
```

??? tip
    Instead of creating the `Clock` yourself you should retrieve it from the [operating system abstraction](https://innmind.org/documentation/getting-started/operating-system/clock/).
