<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

class SiteController extends Controller
{
    public function index()
    {
    	return view('site.index');
    }

    public function assinarPlano(Request $request)
    {
    	$usuario = (new User())->cadastrarUsuario($request);

    	$url = $usuario->assinarPlano($request->plano);

    	if ($url) {
    		return response()->json([
    			'success' => 'true',
    			'url' => $url
    		]);
    	}

    	return response()->json([
    		'success' => 'false',
    		'url' => null
    	]);
    }
}
