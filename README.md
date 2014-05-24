# Add Yaml file support for Laravel 4 Configuration

This package uses Symfony/Yaml parser.

## Installing

Add ```"devitek/yaml-configuration": "*"``` to your **composer.json** by running :

    php composer.phar require devitek/yaml-configuration

And select version : ```0.*```

## Add support in Laravel

You have to replace

`$app = new Illuminate\Foundation\Application;`

with

`$app = new \Devitek\Core\Foundation\Application;`

in **bootstrap/start.php**.

## How to use

Just use regular **php** files or use **yml** or **yaml** files instead.

**PHP** :

```php
<?php

return [
	'debug' => false,
    'key' => 'foobar',
];
```

Will be equivalent to :

**YAML**

```yaml
debug: false
key: foobar
```

Enjoy it ! Feel free to fork :) !
