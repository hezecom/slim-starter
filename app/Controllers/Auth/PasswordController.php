<?php

namespace App\Controllers\Auth;

use App\Auth\Auth;
use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

/**
 * PasswordController
 *
 * @author    Hezekiah O. <support@hezecom.com>
 */
class PasswordController extends Controller
{
    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function createForgotPassword(Request $request, Response $response){
        return view($response,'auth/password/forgot-password.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Delight\Auth\AuthError
     */
    public function forgotPassword(Request $request, Response $response){
        $data = $request->getParsedBody();
        Auth::forgotPassword($data['email']);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Delight\Auth\AuthError
     */
    public function resetPassword(Request $request, Response $response){
        $data = $request->getQueryParams();
        Auth::resetPasswordVerify($data['selector'], $data['token']);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function createUpdatePassword(Request $request, Response $response){
        $data = $request->getQueryParams();
        $selector = $data['selector'];
        $token = $data['token'];
        return view($response,'auth/password/update-password.twig', compact('selector','token'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Delight\Auth\AuthError
     */
    public function updatePassword(Request $request, Response $response){
        $data = $request->getParsedBody();
        $validation = $this->validator->validate($request, [
            'password' => v::notEmpty()->stringType()->length(8),
            'password2' => v::notEmpty(),
        ]);

        if ($validation->failed()) {
            redirect()->route('update.password',[],['selector'=>urlencode($data['selector']),'token'=>urlencode($data['token'])]);
        }

        elseif (!v::equals($data['password'])->validate($data['password2'])) {
            redirect()->route('update.password',[],['selector'=>urlencode($data['selector']),'token'=>urlencode($data['token'])])->with('error','The password do not match.');
        }
        Auth::resetPasswordUpdate($data['selector'], $data['token'], $data['password']);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function createChangePassword(Request $request, Response $response){
        return view($response,'auth/password/change-password.twig');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @throws \Delight\Auth\AuthError
     */
    public function changePassword(Request $request, Response $response){
        $data = $request->getParsedBody();
        $validation = $this->validator->validate($request, [
            'old_password' => v::notEmpty(),
            'new_password' => v::notEmpty()->stringType()->length(8),
        ]);
        if ($validation->failed()) {
            redirect()->route('change.password');
        }
        Auth::changeCurrentPassword($data['old_password'], $data['new_password']);
    }
}
