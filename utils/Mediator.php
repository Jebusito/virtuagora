<?php

class Mediator extends Controller{
  
    public function login($email, $password) {
        static $maxLoginAttempts = 10;
        static $resetTimeMinutes = 60;
        $usuario = Usuario::where('email', $email)->first();
        $unlockTime = $usuario->last_login_attempt;
        $unlockTime->addMinutes($resetTimeMinutes);
        $success = false;
        if (!$usuario) {
            return false;
        }
        if ($unlockTime->gt(Carbon\Carbon::now()) && $usuario->failed_password_attempts >= $maxLoginAttempts) {
            $usuario->failed_password_attempts++;
        } else if ($this->session->login($email, $password)) {
            $success = true;
            $this->update($usuario);
        } else if ($unlockTime->gt(Carbon\Carbon::now())) { //Comprueba si el ultimo intento fue hace poco y lo registra.
            $usuario->failed_password_attempts++;
        } else {
            $usuario->failed_password_attempts = 1; //Caso contrario significa que ya pasó el tiempo de desbloqueo y reinicia el contador.
        }
        $usuario->last_login_attempt = Carbon\Carbon::now();
        $usuario->save();
        return $success;
    }
    
    public function update($usuario = null) {
        $this->generarAviso($usuario);
        $usuario->last_login = Carbon\Carbon::now();
        $usuario->failed_password_attempts = 0;
    }
    
    public function generarAviso($usuario = null){
        $ultimo_login = $usuario->last_login->format('H:i') . 'Hs del ' . $usuario->last_login->format('d-m-Y');
        $mensaje = 'Ingresaste correctamente! Tu ultimo ingreso fue a las ' . $ultimo_login;
        $intentosFallidos = $usuario->failed_password_attempts;
        if ($intentosFallidos == 1) {
            $mensaje .= '. Previamente se ingresó la contraseña erroneamente 1 vez.';
        } else if ($intentosFallidos > 1) {
            $mensaje .= '. Previamente se ingresó la contraseña erroneamente ' . $intentosFallidos . ' veces.';
        }
        $this->flash('success', $mensaje);
     }
}
