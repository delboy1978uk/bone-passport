# bone-passport
Passport package for Bone Mvc Framework
## installation
Use Composer
```
composer require delboy1978uk/bone-passport
```
## usage
Simply add to the `config/packages.php`
```php
<?php

// use statements here
use Bone\Passport\PassportPackage;
use Del\Passport\PassportPackage as DelPassportPackage;

return [
    'packages' => [
        // packages here...,
        DelPassportPackage::class,
        PassportPackage::class,
    ],
    // ...
];
```
