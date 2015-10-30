<?php

class Mediator extends Controller{
  
    public function login($email, $password) {
        //Dos variables estaticas como parametros que determinan el comportamiento del Patrón
        static $maxLoginAttempts = 10; //Cantidad maxima de intentos de logueo previo a un bloqueo
        static $resetTimeMinutes = 60; //Tiempo en minutos a esperar en caso de estar bloqueado
        
        $usuario = Usuario::where('email', $email)->first(); //Obtenemos el usuario actual
        if (!$usuario) {
            return false; //Por control si no se encontró usuario, se termina la funcion.
        }
        $unlockTime = $usuario->last_login_attempt; //Tomamos el momento del ultimo intento de login
        $unlockTime->addMinutes($resetTimeMinutes); //Y le agregamos el tiempo de reset elegido.
        $success = false; //Variable bandera, se hace verdadera solo si el login es correcto.

        /*Primero comprobamos si está bloqueado:
         * Si no pasa el tiempo de reset (es $unlockTime greater than Now)
         * Y al mismo tiempo tenemos mas intentos que los permitidos.
         * Si se dan ambas, no se chequea la contraseña, solo se aumenta el contador de intentos.
         */
        if ($unlockTime->gt(Carbon\Carbon::now()) && $usuario->failed_password_attempts >= $maxLoginAttempts) {
            $usuario->failed_password_attempts++;
        /* Caso contrario se comprueba el usuario y contraseña*/
        } else if ($this->session->login($email, $password)) {
            $success = true;
            $this->update($usuario); //Llama a la funcion update.
        /*En el caso de que el usuario ingrese mal la contraseña 
         *y el ultimo intento fue cercano, se incrementa el contador 
         */
        } else if ($unlockTime->gt(Carbon\Carbon::now())) { //Comprueba si el ultimo intento fue hace poco y lo registra.
            $usuario->failed_password_attempts++;
        /* El ultimo caso hace referencia a que ya pasó el tiempo de desbloqueo
         * e igualmente el usuario ingresó mal la contraseña o se equivocó
         * por primera vez desde que creó la cuenta. 
         */
        } else {
            $usuario->failed_password_attempts = 1; //Caso contrario significa que ya pasó el tiempo de desbloqueo y reinicia el contador.
        }
        $usuario->last_login_attempt = Carbon\Carbon::now();
        $usuario->save();
        return $success;
    }
    
    public function update($usuario = null) {
        /*Se genera un mensaje para el usuario con los datos actuales
        * Ultima vez que entró y cuantos intentos hubo.*/
        $this->generarAviso($usuario);
        //Luego se resetean los datos por el login correcto
        $usuario->last_login = Carbon\Carbon::now();
        $usuario->failed_password_attempts = 0;
    }
    
    public function generarAviso($usuario = null) {
        $mensaje = 'Ingresaste correctamente!';
        if (!$usuario->last_login->isToday()) { //Si el usuario ya ingresó en el dia, se omite la informacion de ultimo ingreso
            $ultimo_login = $usuario->last_login->format('H:i') . 'Hs del ' . $usuario->last_login->format('d/m/Y');
            $mensaje.= ' Tu ultimo ingreso fue a las ' . $ultimo_login;   
        }
        $intentosFallidos = $usuario->failed_password_attempts;
        if ($intentosFallidos == 1) {
            $mensaje .= '. Previamente se ingresó la contraseña erroneamente 1 vez.';
        } else if ($intentosFallidos > 1) {
            $mensaje .= '. Previamente se ingresó la contraseña erroneamente ' . $intentosFallidos . ' veces.';
        }
        $this->flash('success', $mensaje);
     }
     
     //Se llama cuando el usuario se registra por primera vez
     public function initVariablesReintentos($usuario){
        $usuario->last_login = Carbon\Carbon::now();
        $usuario->failed_password_attempts = 0;
        $usuario->last_login_attempt = Carbon\Carbon::now();
     }
}
