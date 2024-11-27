<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\User;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class loginController extends Controller
{


    public function login()
    {
        $role = "";
        $idkey = "";

        // ahora nada más es replicar
        
        $correo = request()->json("correo");
        $contrasena = request()->json("contrasena");


        // Validar si no están vacios
        if ($correo == null) {
            return response()->json( ["correo"=> $correo , "mensaje"=> "correo no valido"] );
        }
        if($contrasena == null){
            return response()->json( ["contraseña"=> $contrasena, "mensaje"=> "contraseña no valida"] );
        }
        
        

        // Consulta a la DB
        $usuario = User::where("correo", $correo)->first();


        if (!$usuario) {
            return response("el correo no existe", 403);
        }

        $verificarPsw = Hash::check($contrasena, $usuario->contrasena);

        if (!$verificarPsw) {
            return response("la contraseña no coincide", 403);
        }

        try {
            // Hacer la solicitud GET a la API externa
            $response = Http::get("http://localhost:3005/compradores/user/{$usuario->idUsuario}");

            // Verificar si la respuesta fue exitosa (status code 200)
            if ($response->successful()) {
                $compradorData = $response->json();  // Parseamos la respuesta como JSON

                if (count($compradorData) > 0) {
                    $comprador = $compradorData[0];  // Accedemos al primer elemento del array
                    $role = 'comprador';
                    $idkey = ['idComprador' => $comprador['idComprador']];  // Accedemos a idComprador
                } else {
                    // Si no se encuentra el comprador
                    echo "No se encontró comprador para este usuario";
                }
            } else {
                // Si la respuesta no fue exitosa, puedes manejar el error
                echo "error al obtener datos del comprador";
                response()->status(403);
            }
        } catch (\Exception $e) {
            
            // Manejar excepciones en caso de error en la solicitud
            echo "El usuario no posee rol comprador" .  $e->getMessage();
        }

        try {
            // Hacer la solicitud GET a la API externa
            $response = Http::get("http://localhost:3005/producers/user/{$usuario->idUsuario}");

            // Verificar si la respuesta fue exitosa (status code 200)
            if ($response->successful()) {
                $productorData = $response->json();  // Parseamos la respuesta como JSON

                if (count($productorData) > 0) {
                    $productor = $productorData[0];  // Accedemos al primer elemento del array
                    $role = 'productor';
                    $idkey = ['idProductor' => $productor['idProductor']];  // Accedemos a idProductor
                } else {
                    // Si no se encuentra el comprador
                    echo "No se encontró comprador para este usuario";
                }
            } else {
                // Si la respuesta no fue exitosa, puedes manejar el error
                echo "error al obtener datos del comprador";
                response()->status(403);
            }
        } catch (\Exception $e) {
            // Manejar excepciones en caso de error en la solicitud
            echo "El usuario no posee rol comprador" .  $e->getMessage();
        }


        // Una variable que solo tendrá la ruta de la vista en react 

        $redirectUrl = "";
        $payload = "";

        if ($role === "comprador") {
            $redirectUrl = "/MarketPlaceComp";
            $payload= [
                "id"=> $usuario->idUsuario,
                "role"=> $role,
                "idComprador"=> $idkey["idComprador"]
            ];
        }else{
            $redirectUrl = "/principalCards";
            $payload= [
                "id"=> $usuario->idUsuario,
                "rol"=> $role,
                "idProductor"=> $idkey["idProductor"]
            ];
        }


        

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        return response()->json([
            'message' => 'Inicio de sesion exitoso',
            'token' => $token,
            'redirectUrl' => $redirectUrl,
            $payload
        ]);
    }
}
