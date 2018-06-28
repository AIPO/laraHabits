<?php

namespace App\Http\Controllers\api;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        $request->validate(
            [
                'email' => 'required',
                'name' => 'required',
                'password' => 'required',
            ]
        );
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        $guzzle = new Client;

        $response = $guzzle->post(
            url('oauth/token'),
            [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => '2',
                    'client_secret' => 'j5xPRWs5I5BOTWpY2aZa9uNERCmY8MKAraWeTEwR',
                    'email' => $request->email,
                    'password' => $request->password,
                    'scope' => '*',
                ],
            ]
        );

        return response(['auth' => json_decode((string)$response->getBody(), true), 'user' => $user]);
    }

    public function login(Request $request)
    {
        $request->validate(
            [
                'email' => 'required',
                'password' => 'required',
            ]
        );
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return new JsonResponse(['message' => 'User doesn\'t exist']);
        }

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

            return json_decode((string) $response->getBody(), true);
        } else {
            return new JsonResponse(['message' => 'User password is wrong.']);
        }
    }
}
