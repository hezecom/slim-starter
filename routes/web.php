<?php
use App\Controllers\Auth\AuthController;
use App\Controllers\Auth\PasswordController;
use App\Controllers\HomeController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

$app->get('/', HomeController::class .':index')->setName('index');

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


$app->group('', function ($route) {
    $route->get('/dashboard', HomeController::class .':dashboard')->setName('home');
    $route->get('/logout', AuthController::class . ':logout')->setName('logout');
    $route->get('/change-password', PasswordController::class . ':createChangePassword')->setName('change.password');
    $route->post('/change-password', PasswordController::class . ':changePassword');
})->add(new AuthMiddleware($container));



