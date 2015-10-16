<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mediator
 *
 * @author Ignacio Ramos
 */
class Mediator {
    #Declaramos dos variables estaticas que van a determinar los parametros del patron de bloqueo por contrasenia.
    protected static $maxLoginAttempts, $resetTimeMinutes;
            
    public function __construct() {
    #Inicializamos las variables, al ser un singleton el Mediator requiere reiniciar el servidor si se cambian.
        $this->$maxLoginAttempts = 10;
        $this->$resetTimeMinutes = 60;       
    }
    
    public function login($email, $password) {
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
        $_SESSION['user']['ultimo_login'] = $usuario->last_login;
        $usuario->last_login = Carbon\Carbon::now();
        $usuario->failed_password_attempts = 0;
    }
    
    public function generarAviso($portal){
        $mensaje = 'Ingresaste correctamente! Ultimo ingreso: ' + $portal->session->user('ultimo_login');
        $intentosFallidos = $this->session->user('intentos');
        if ($intentosFallidos == 1) {
            $mensaje .= ' previamente se ingresó la contraseña erroneamente 1 vez.';
        } else if ($intentosFallidos > 1) {
            $mensaje .= ' previamente se ingresó la contraseña erroneamente ' . $intentosFallidos . ' veces.';
        }
        $this->flash('success', $mensaje);
        $this->redirectTo('shwPortal');
     }
}
