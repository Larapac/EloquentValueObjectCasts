# Eloquent ValueObject Casts

```php
use Illuminate\Database\Eloquent\Model;
use Larapac\EloquentValueObjectCasts\CastsValueObjectsTrait.php;

class Foo extend Model
{
    use CastsValueObjectsTrait

    protected $casts = [
        'is_bar' => 'boolean',
        'options' => FooOptions::class,
        'metadata' => ModelsMetadata::class,
    ];

    //...
}
```
