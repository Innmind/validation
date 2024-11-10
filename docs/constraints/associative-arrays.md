# Associative arrays

Here's the constraint to validate the HTTP response <https://packagist.org/packages/list.json?vendor=innmind&fields[]=repository&fields[]=abandoned>:

```php
use Innmind\Validation\Is;

$validate = Is::associativeArray(
    Is::string(),
    Is::shape('repository', Is::string())
        ->with('abandoned', Is::bool()->or(Is::string())),
);
```
