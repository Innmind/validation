# Dates

This will transform `string`s into `PointInTime`s from the [`innmind/time-continuum` package](https://github.com/Innmind/TimeContinuum/).

```php
use Innmind\Validation\PointInTime;
use Innmind\TimeContinuum\Earth\{
    Clock,
    Format\ISO8601,
};

$validate = PointInTime::ofFormat(
    new Clock,
    new ISO8601,
);
```

??? tip
    Instead of creating the `Clock` yourself you should retrieve it from the [operating system abstraction](https://innmind.org/documentation/getting-started/operating-system/clock/).
