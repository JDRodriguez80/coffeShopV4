<?php

namespace App\Http\Controllers;

use App\Models\Otp;
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
}
