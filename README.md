No Magic Properties
==================

This is a Laravel to enable the use of public properties in model, but also retaining default laravel magic.


Installation
------------
Install with [composer](https://getcomposer.org/):

```sh
composer require hallekamp/laravel-nomagicproperties
```


Usage
-----
Just include the trait in your models.
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hallekamp\NoMagicProperties\Traits\NoMagicProperties;

class NoMagicPropertiesModel extends Model
{
    use NoMagicProperties;
    ...
}
```


License
-------
The files in this archive are licensed under the MIT license.
You can find a copy of this license in [LICENSE.txt](LICENSE.txt).
