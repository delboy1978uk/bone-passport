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
Now run the bone command to pick up database changes, creating a database migration, then run them:
```
bone migrant:diff
bone migrant:migrate
```
### roles
Roles can be set via the command line using the `bone` command
```
bone passport:role --help

Description:
  Manages roles.

Usage:
  passport:role <operation> <role>

Arguments:
  operation             add or remove
  role                  The role name
```
You can assign a role to a user again by using the `bone` command
```
bone passport:admin --help

Description:
  User role admin.

Usage:
  passport:admin <operation> <role> <userId> [<entityId>]

Arguments:
  operation             grant or revoke
  role                  The role name
  userId                The ID of the user
  entityId              The ID of the entity, if any
```
### middleware
Bone Passport comes with an easy to use middleware to secure your routes. Anyone without the required credentionals will
receive a 403 unauthorised response. 

In your `addRoutes()` method in the package class, create the middleware first:
```php
public function addRoutes(Container $c, Router $router): Router
{
    $passportControl = $c->get(PassportControl::class);
    $middleware = new PassportControlMiddleware($passportControl);

    // routes here
}
```
To secure an endpoint, you should already be using a middleware such as `SessionAuth`, which provides a PSR-7 
RequestInterface with a User object set as the `user` attribute. Add to the array of middlware with the options you 
require:
```php
$router->map('GET', '/lock-me', [MyController::class, 'someAction'])->middlewares([
    $c->get(SessionAuth::class), 
    $middleware->withOptions('admin')
]);
```
### entity ids
Sometimes a role will only be in control of various specific entities. For example, a league admin would be in charge 
of specific clubs in that league. Usually the ID of that entity will be in the URL. You can lock down this by passing 
the route variable name, like so:
```php
$router->map('GET', '/books/edit/{id:number}', [MyController::class, 'someAction'])->middlewares([
    $c->get(SessionAuth::class), 
    $middleware->withOptions('book-admin', 'id')
]);
```

