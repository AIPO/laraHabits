<?php

namespace App\Http\Controllers\api;

use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|string|email|unique:users',
                'name' => 'required|string',
                'password' => 'required|string|confirmed',
            ]
        );

        $user = new User(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]
        );
        $user->save();


        return response()->json(
            [
                'message' => 'Successfully created a user!',
            ],
            201
        );
    }

    public function login(Request $request)
    {
        $request->validate(
            [
                'email' => 'required',
                'password' => 'required',
                'remember_me' => 'boolean',
            ]
        );
        $credentials = request(['email', 'password']);

        if (Auth::attempt($credentials)) {
            return response()->json(
                [
                    'message' => 'Unauthorized',
                ]
            );
        }

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
$token->save();
        if (Hash::check($request->password, $user->password)) {
            $guzzle = new Client;

            $response = $guzzle->post(
                url('oauth/token'),
                [
                    'form_params' => [
                        'grant_type' => 'password',
                        'client_id' => '2',
                        'client_secret' => 'OMJIMN1Z0XrjJvvtXlRjHge8j1PfxglqKpYyMMLt',
                        'email' => $request->email,
                        'password' => $request->password,
                        'scope' => '',
                    ],
                ]
            );

            return json_decode((string)$response->getBody(), true);
        } else {
            return new JsonResponse(['message' => 'User password is wrong.']);
        }
    }
}
