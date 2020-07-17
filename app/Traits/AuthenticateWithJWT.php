<?php

namespace App\Traits;

trait AuthenticateWithJWT
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(string $auth_driver)
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth($auth_driver)->attempt($credentials)) {
            return response()->error('Invalid credentials!', 401);
        }

        $user = auth($auth_driver)->user();
        if ($user->status != 'active') {
            return response()->error('Invalid credentials!', 401);
        }

        return $this->respondWithTokenAndUser($auth_driver, $token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(string $auth_driver)
    {
        auth($auth_driver)->logout();

        return response()->success(true);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(string $auth_driver)
    {
        return $this->respondWithTokenAndUser($auth_driver, auth($auth_driver)->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithTokenAndUser(string $auth_driver, $token)
    {
        return response()->success([
            'access_token' => $token,
            'user' => auth($auth_driver)->user()
        ]);
    }
}
