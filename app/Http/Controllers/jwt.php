<?php

// use Tymon\JWTAuth\Facades\JWTAuth;
// use Illuminate\Http\Request;

// class AuthController extends Controller
// {
//     public function login(Request $request)
//     {
//         // Validar las credenciales (puedes ajustar la lógica según tu necesidad)
//         $user = User::where('idUsuario', $request->idUsuario)->first();

//         if (!$user) {
//             return response()->json(['message' => 'Usuario no encontrado'], 404);
//         }

//         // Otros datos que puedas querer agregar al payload del JWT
//         $role = $user->role;  // Por ejemplo, asumiendo que tienes un campo 'role' en tu modelo
//         $idKey = $user->idKey; // Si quieres agregar un campo adicional

//         // Crear el payload con los datos del usuario
//         $payload = [
//             'id' => $user->idUsuario,
//             'role' => $role,
//             'idKey' => $idKey
//         ];

//         // Crear el token (similar a jwt.sign)
//         $token = JWTAuth::fromUser($user, ['exp' => now()->addHours(2)->timestamp]);

//         return response()->json([
//             'token' => $token
//         ]);
//     }
// }

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
