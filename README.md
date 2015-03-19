# Add Yaml file support for Laravel 5 Configuration

This package uses Symfony/Yaml parser.

## Installing

Add ```"devitek/yaml-configuration": "1.*"``` to your **composer.json** by running :

    php composer.phar require devitek/yaml-configuration

And select version : ```1.*```

## Add support in Laravel

You have to replace

`$app = new Illuminate\Foundation\Application(`

with

`$app = new Devitek\Core\Foundation\Application(`

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

## Paths Helpers

You can use paths helpers provided by Laravel like that :

```yaml
routes_file: %app_path%/routes.php
unit_test: %base_path%/behat.yml
main_style: %public_path%/css/style.css
manifest: %storage_path%/meta
```

* %app\_path% refers to app\_path()
* %base\_path% refers to base\_path()
* %public\_path% refers to public\_path()
* %storage\_path% refers to storage\_path()

Enjoy it ! Feel free to fork :) !
