# Slim Framework 4 Starter App

> Slim Framework 4 skeleton MVC application with build in authentication system.

## Features

 * Light weight and organised for easy understanding.
 * Simple, fast routing engine.
 * Highly secured, ready to use authentication system.
 * Simplify helper function for faster development.
 * Build in email notification.
 * Build in email templates (can easily be modified)
 * PSR-7 implementation and PHP-DI container implementation
 * Detailed HTML error reporting.

## Requirements

 * PHP 7.4 | 8.0+
 * PDO PHP Extension
 * Suport MySQL 5.5.3+ **or** MariaDB 5.5.23+ **or** PostgreSQL 9.5.10+ **or** SQLite 3.14.1+ ...

## Installation



```shell
composer create-project "hezecom/slim-starter v1.0" [my-app]
```
**`Database (Required for auth to work)`**
* Create a database or use existing database to import/copy to your SQL console to create required tables for the authentication.
* Dasbase option (MySQL, SQLite or PostgreSQL)
* Database file is located at **/`database`**

## .env

Copy file .env.example to .env

```
DB_DRIVER=mysql
DB_HOST=localhost
DB_DATABASE=slimapp
DB_USERNAME=root
DB_PASSWORD=
DB_PORT=3306

# Email setting Driver = (smtp | sendmail | mail)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=username
MAIL_PASSWORD=password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS='info@example.com'
MAIL_FROM_NAME='Example'
```

## Router

Routing example below

```php
<?php

$app->get('/', 'HomeController:index')->setName('home');

$app->group('', function ($route) {
    $route->get('/register', AuthController::class . ':createRegister')->setName('register');
    $route->post('/register', AuthController::class . ':register');
    $route->get('/login', AuthController::class . ':createLogin')->setName('login');
    $route->post('/login', AuthController::class . ':login');

    $route->get('/verify-email', AuthController::class.':verifyEmail')->setName('verify.email');
    $route->get('/verify-email-resend',AuthController::class.':verifyEmailResend')->setName('verify.email.resend');

    $route->get('/forgot-password', PasswordController::class . ':createForgotPassword')->setName('forgot.password');
    $route->post('/forgot-password', PasswordController::class . ':forgotPassword');
    $route->get('/reset-password', PasswordController::class.':resetPassword')->setName('reset.password');
    $route->get('/update-password', PasswordController::class.':createUpdatePassword')->setName('update.password');
    $route->post('/update-password', PasswordController::class.':updatePassword');

})->add(new GuestMiddleware($container));
```

## Controller

Controller example simplify
```php
<?php

namespace App\Controllers;

class HomeController extends Controller
{
	public function index(Request $request, Response $response)
	{
		return view($response,'index.twig');
	}
}
```

## Model

Uses Eloquent ORM used by Laravel Framework. It currently supports MySQL, Postgres, SQL Server, and SQLite.
Reference - [illuminate/database](https://github.com/illuminate/database)
```php
<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $table = 'users';

	protected $fillable = [
		'email',
		'username',
		'password',
	];

}
```

## Middleware

```php
<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware extends Middleware
{
	public function __invoke(Request $request, RequestHandler $handler)
	{
	   if(! $this->container->get('auth')->isLogin()) {
              return redirect()->route('login')->with('error', 'Access denied, you need to login.');
            }
        $response = $handler->handle($request);
        return $response;
	}
}
```

## Validation

Use the most awesome validation engine ever created for PHP.
Reference - [Respect/Validation](https://github.com/Respect/Validation)
```php
<?php
namespace App\Controllers\Auth;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class AuthController extends Controller
{
	public function register(Request $request, Response $response)
	{
		$validation = $this->validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty()->email(),
                        'username' => v::noWhitespace()->notEmpty()->alnum(),
                        'password' => v::notEmpty()->stringType()->length(8),
		]);

		if ($validation->failed()) {
		    redirect()->route('register');
		}

		//	more coding here
	}
}
```

## More basic functions

reference slim official documents - [Slim Framework](http://www.slimframework.com/docs/)



## Directory Structure

```shell
|-- slim-born
	|-- app
		|-- Auth
		|-- Controllers
		|-- Middleware
		|-- Models
		|-- Lib
	    |-- bootstrap
		|-- app.php
        |-- database.php
        |-- helper.php
    |-- logs
	|-- public
	|-- resources
    |-- route
	....
```

## Testing

``` bash
$ phpunit
```

## Contributing

All contributions are welcome! If you wish to contribute, please create an issue first so that your feature, problem or question can be discussed.

## License

This project is licensed under the terms of the [MIT License](https://opensource.org/licenses/MIT).
