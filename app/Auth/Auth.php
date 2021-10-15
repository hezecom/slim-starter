<?php

namespace App\Auth;

use App\Lib\Mail;
use Delight\Auth\ConfirmationRequestNotFound;
use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\InvalidSelectorTokenPairException;
use Delight\Auth\NotLoggedInException;
use Delight\Auth\ResetDisabledException;
use Delight\Auth\TokenExpiredException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UserAlreadyExistsException;

/**
 * Auth
 *
 * @author    Hezekiah O. <support@hezecom.com>
 */
class Auth
{
    static protected $auth;

    /**
     * Auth constructor.
     */
    public function __construct()
    {
        self::$auth = auth();
    }

    /**
     * @param $email
     * @param $username
     * @param $password
     * @param array $info
     * @return int
     * @throws \Delight\Auth\AuthError
     */
    public static function create($email, $username, $password, $info=[]){
        $auth = self::$auth;
        try {
            $userId = $auth->register($email, $username, $password, function ($selector, $token) use ($email, $username) {
                $link = url('verify.email',[],['selector'=>urlencode($selector),'token'=>urlencode($token)]);
                $message = file_get_contents(__DIR__.'/../../resources/views/auth/mail/confirm-email.html');
                $message = str_replace(['{link}','{app_name}'],[$link,envi('APP_NAME')],$message);
                $subject = 'Email Verification';
                $from = ['email'=>envi('MAIL_FROM_ADDRESS'), 'name'=>envi('APP_NAME')];
                $to = ['email'=>$email, 'name'=>$username];
                // send message
                Mail::send($subject, $message, $from, $to);
            });
            //$auth->admin()->addRoleForUserById($userId, Role::ADMIN);
            return $userId;
        }
        catch (InvalidEmailException $e) {
            redirect()->route('register')->with('error','Invalid email address');
        }
        catch (InvalidPasswordException $e) {
            redirect()->route('register')->with('error','Invalid password');
        }
        catch (UserAlreadyExistsException $e) {
            redirect()->route('register')->with('error','User already exists test');
        }
        catch (TooManyRequestsException $e) {
            redirect()->route('register')->with('error','Too many requests, try again later');
        }
    }

    /**
     * @param $selector
     * @param $token
     * @throws \Delight\Auth\AuthError
     */
    public static function verifyEmail($selector, $token){
        $auth = self::$auth;
        try {
            $auth->confirmEmail($selector, $token);
            //echo 'Email address has been verified';
            redirect()->route('login')->with('success','Email address has been verified');
        }
        catch (InvalidSelectorTokenPairException $e) {
            redirect()->route('login')->with('error','Invalid token');
        }
        catch (TokenExpiredException $e) {
            redirect()->route('login')->with('error','Token expired');
        }
        catch (UserAlreadyExistsException $e) {
            redirect()->route('login')->with('error','Email address already exists');
        }
        catch (TooManyRequestsException $e) {
            redirect()->route('login')->with('error','Too many requests, try again later.');
        }
    }

    /**
     * Re-sending confirmation requests
     * @param $email
     */
    public static function ResendVerification($email){
        $auth = self::$auth;
        try {
            $auth->resendConfirmationForEmail($email, function ($selector, $token) use ($email) {
                $link = url('verify.email',[],['selector'=>urlencode($selector),'token'=>urlencode($token)]);
                $message = file_get_contents(__DIR__.'/../../resources/views/auth/mail/confirm-email.html');
                $message = str_replace(['{link}','{app_name}'],[$link,envi('APP_NAME')],$message);
                $subject = 'Email Verification';
                $from = ['email'=>envi('MAIL_FROM_ADDRESS'), 'name'=>envi('MAIL_FROM_NAME')];
                $to = ['email'=>$email, 'name'=>''];
                // send message
                Mail::send($subject, $message, $from, $to);
            });
            redirect()->route('login')->with('success','We have sent you another email. Please follow the link to verify your email.');
        }
        catch (ConfirmationRequestNotFound $e) {
            redirect()->route('login')->with('error','No earlier request found that could be re-sent.');
        }
        catch (TooManyRequestsException $e) {
            redirect()->route('login')->with('error','Too many requests, try again later');
        }
    }
    /**
     * @param $email
     * @param $password
     * @param null $remember
     * @throws \Delight\Auth\AttemptCancelledException
     * @throws \Delight\Auth\AuthError
     */
    public static function login($email, $password, $remember=null){
        $auth = self::$auth;
        try {
            if ($remember !='') {
                // keep logged in for one year
                $rememberDuration = (int) (60 * 60 * 24 * 365.25);
            }
            else {
                // do not keep logged in after session ends
                $rememberDuration = null;
            }

            $auth->login($email, $password,$rememberDuration);
            return true;
        }
        catch (InvalidEmailException $e) {
            redirect()->route('login')->with('error','Wrong email address');
        }
        catch (InvalidPasswordException $e) {
            redirect()->route('login')->with('error','Wrong password');
        }
        catch (EmailNotVerifiedException $e) {
            redirect()->route('login')->with('error','Email not verified');
            die('Email not verified');
        }
        catch (TooManyRequestsException $e) {
            redirect()->route('login')->with('error','Too many requests');
        }
    }

    /**
     * Reset Password 1 of 3
     * @param $email
     * @throws \Delight\Auth\AuthError
     */
    public static function forgotPassword($email){
        $auth = self::$auth;
        try {
            $auth->forgotPassword($email, function ($selector, $token) use ($email) {
                $link = url('reset.password',[],['selector'=>urlencode($selector),'token'=>urlencode($token)]);
                $message = file_get_contents(__DIR__.'/../../resources/views/auth/mail/reset-password.html');
                $message = str_replace(['{link}','{app_name}'],[$link,envi('APP_NAME')],$message);
                $subject = 'Reset Password';
                $from = ['email'=>envi('MAIL_FROM_ADDRESS'), 'name'=>envi('MAIL_FROM_NAME')];
                $to = ['email'=>$email, 'name'=>''];
                // send message
                Mail::send($subject, $message, $from, $to);
            });
            redirect()->route('forgot.password')->with('success','A password reset link has been sent to your email.');
        }
        catch (InvalidEmailException $e) {
            redirect()->route('forgot.password')->with('error','Invalid email address');
        }
        catch (EmailNotVerifiedException $e) {
            redirect()->route('forgot.password')->with('error','Email not verified');
        }
        catch (ResetDisabledException $e) {
            redirect()->route('forgot.password')->with('error','Password reset is disabled');
        }
        catch (TooManyRequestsException $e) {
            redirect()->route('forgot.password')->with('error','Too many requests, try again later');
        }
    }

    /**
     * Reset Password 2 of 3
     * @param $selector
     * @param $token
     * @throws \Delight\Auth\AuthError
     */
    public static function resetPasswordVerify($selector, $token){
        $auth = self::$auth;
        try {
            $auth->canResetPasswordOrThrow($selector, $token);
            redirect()->route('update.password',[],['selector'=>urlencode($selector),'token'=>urlencode($token)]);
        }
        catch (InvalidSelectorTokenPairException $e) {
            redirect()->route('forgot.password')->with('error','Invalid token');
        }
        catch (TokenExpiredException $e) {
            redirect()->route('forgot.password')->with('error','Token expired');
        }
        catch (ResetDisabledException $e) {
            redirect()->route('forgot.password')->with('error','Password reset is disabled');
        }
        catch (TooManyRequestsException $e) {
            redirect()->route('forgot.password')->with('error','Too many requests, try again later');
        }
    }

    /**
     * Reset Password 3 of 3
     * @param $selector
     * @param $token
     * @param $password
     * @throws \Delight\Auth\AuthError
     */
    public static function resetPasswordUpdate($selector, $token, $password){
        $auth = self::$auth;
        try {
            $auth->resetPassword($selector, $token, $password);
            redirect()->route('login')->with('success','Password has been reset');
        }
        catch (InvalidSelectorTokenPairException $e) {
            redirect()->route('update.password',[],['selector'=>urlencode($selector),'token'=>urlencode($token)])->with('error','Invalid token');
        }
        catch (TokenExpiredException $e) {
            redirect()->route('update.password',[],['selector'=>urlencode($selector),'token'=>urlencode($token)])->with('error','Token expired');
        }
        catch (ResetDisabledException $e) {
            redirect()->route('update.password',[],['selector'=>urlencode($selector),'token'=>urlencode($token)])->with('error','Password reset is disabled');
        }
        catch (InvalidPasswordException $e) {
            redirect()->route('update.password',[],['selector'=>urlencode($selector),'token'=>urlencode($token)])->with('error','Invalid password');
        }
        catch (TooManyRequestsException $e) {
            redirect()->route('login')->with('error','Too many requests, try again later');
        }
    }

    /**
     * Changing the current user’s password when logged in only
     * @param $oldPassword
     * @param $newPassword
     * @throws \Delight\Auth\AuthError
     */
    public static function changeCurrentPassword($oldPassword, $newPassword){
        $auth = self::$auth;
        try {
            $auth->changePassword($oldPassword, $newPassword);
            redirect()->route('home')->with('success','Password has been changed');
        }
        catch (NotLoggedInException $e) {
            redirect()->route('change.password')->with('error','You are not logged in');
        }
        catch (InvalidPasswordException $e) {
            redirect()->route('change.password')->with('error','Your old password do not match');
        }
        catch (TooManyRequestsException $e) {
            redirect()->route('change.password')->with('error','Too many requests, try again later');
        }
    }

    /**
     * @throws \Delight\Auth\AuthError
     */
    public static function logout(){
        return self::$auth->logOut();
    }

    /**
     * @return bool
     */
    public function isLogin(){
        if (self::$auth->isLoggedIn()) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function user(){
        $auth = self::$auth;
        $info = [
            'id' => $auth->getUserId(),
            'email' => $auth->getEmail(),
            'username' => $auth->getUsername(),
            'ip' => $auth->getIpAddress()
        ];
        return $info;
    }
}
