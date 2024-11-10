---
hide:
    - navigation
    - toc
---

# Philosophy

In this package a validation rule is called a `Constraint`.

All constraints are immutable objects. This means that you can compose them any way you want safely.

Unlike most validation packages, here you can directly apply transformation on each validated value (1). This brings 2 benefits:
{.annotate}

1. via the `->map()` methods

<div markdown>
- no need to write `if`s after validation when transforming the data to please static analysis tools
- no need to have classes that _look like_ valid data just to declare validations rules
</div>

This helps have a clear separation between declaring the expected structure (1) and the execution.
{.annotate}

1. both the input and the output

Another benefit of contraints being immutable objects is that there's no central _validator_ object. This means that anyone can create its own `Constraint` and use it directly.
