<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
/**
 * HomeController
 * @author    Hezekiah O. <support@hezecom.com>
 */
class HomeController extends Controller
{
	public function index(Request $request, Response $response)
	{
        return view($response,'index.twig');
	}

    public function dashboard(Request $request, Response $response)
    {
        $users = User::limit(10)->get();
        return view($response,'admin/dashboard/index.twig', compact('users'));
    }
}
