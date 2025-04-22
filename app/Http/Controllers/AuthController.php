<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\Usuario;
use App\Services\TwilioService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function enviarOTP(Request $request, TwilioService $twilio)
    {
        //recibiendo el numero del cliente
        $request->validate([
            'telefono' => 'required',
        ]);
        $codigo = rand(100000, 999999);
        $mensaje= "Tu Codigo de verificacion es: ".$codigo;
        //enviando a tabla OTP
        $otp= OTP::firstOrNew(['telefono'=>request('telefono')],
            ['codigo'=>$codigo],
        );
        $otp->expira_en=now()->addMinutes(5);
        $otp->save();

        try {
            $twilio->enviarWhatsApp($request->telefono, $mensaje);
            return response()->json(['mensaje' => 'Codigo de verificacion enviado'], 200);
        }catch (\Exception $exception){
            return response()->json(['mensaje' => $exception->getMessage()], 500);
        }

    }
    //verificando el codiogo y telefono
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'telefono' => 'required|string',
            'codigo_otp' => 'required|string',
        ]);

        $registro = Usuario::where('telefono',$request->telefono)
            ->where('codigo_otp', $request->codigo_otp)
            ->where('expira_en','>',now())
            ->first();
        if(!$registro){
            return response()->json(['error'=>'codigo invalido o expirado'],401);
        }
        return response()->json(['message'=>'codigo valido'], 200);
    }

    //registrando al usuario
    public function registrar(Request $request)
    {
        $request->validate([
            'telefono' => 'required|string|unique:usuarios',
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'password' => 'required|string',
            'nick' => 'required|string|unique:usuarios',
            'email' => 'required|email|unique:usuarios',
            'direccion' => 'required|string',
        ]);
        $usuario = Usuario::create([
            'telefono' => $request->telefono,
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'nick' => $request->nick,
            'email' => $request->email,
            'direccion' => $request->direccion,
            'password' => bcrypt($request->password),
        ]);
        return response()->json(['message' => 'Registro exitoso', 'usuario' => $usuario]);
    }
}
