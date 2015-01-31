<?php use Augusthur\Validation as Validate;

class UsuarioCtrl extends Controller {

    public function verCambiarClave() {
        $this->render('perfil/cambiar-clave.twig');
    }

    public function cambiarClave() {
        $vdt = new Validate\Validator();
        $vdt->setLabel('pass-old', 'La contraseña actual')
            ->setLabel('pass-new', 'La contraseña nueva')
            ->addRule('pass-old', new Validate\Rule\MatchesPassword($usuario->password))
            ->addRule('pass-new', new Validate\Rule\MinLength(8))
            ->addRule('pass-new', new Validate\Rule\MaxLength(128))
            ->addRule('pass-new', new Validate\Rule\Matches('pass-verif'));
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        if (!$this->session->login($this->session->user('email'), $vdt->getData('pass-old'))) {
            throw (new TurnbackException())->setErrors(array('Contraseña inválida.'));
        }
        $usuario = $this->session->getUser();
        $usuario->password = password_hash($vdt->getData('pass-new'), PASSWORD_DEFAULT);
        $usuario->save();
        $this->flash('success', 'Su contraseña fue modificada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function verModificar() {
        $usuario = $this->session->getUser();
        $this->render('contenido/propuesta/modificar.twig', array('usuario' => $usuario->toArray()));
    }

    public function modificar() {
        $vdt = new Validate\Validator();
        $vdt->addRule('nombre', new Validate\Rule\Alpha(array(' ')))
            ->addRule('nombre', new Validate\Rule\MinLength(1))
            ->addRule('nombre', new Validate\Rule\MaxLength(32))
            ->addRule('apellido', new Validate\Rule\Alpha(array(' ')))
            ->addRule('apellido', new Validate\Rule\MinLength(1))
            ->addRule('apellido', new Validate\Rule\MaxLength(32))
            ->addRule('url', new Validate\Rule\URL())
            ->addRule('email', new Validate\Rule\Email())
            ->addRule('telefono', new Validate\Rule\Telephone())
            ->addOptional('url')
            ->addOptional('email')
            ->addOptional('telefono');
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        $usuario = $this->session->getUser();
        $usuario->nombre = $vdt->getData('nombre');
        $usuario->apellido = $vdt->getData('apellido');
        $usuario->save();
        $contacto = $usuario->contacto ?: new Contacto;
        $contacto->email = $vdt->getData('email');
        $contacto->web = $vdt->getData('url');
        $contacto->telefono = $vdt->getData('telefono');
        $contacto->save();
        $this->flash('success', 'Sus datos fueron modificados exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function cambiarImagen() {
        $usuario = $this->session->getUser();
        $usuario->img_tipo = 0;
        $usuario->img_hash = $usuario->id;
        $usuario->save();
        ImageManager::crearImagen('usuario', $usuario->id, array(32, 64, 160));
        $this->session->setUser($usuario);
        $this->flash('success', 'Imagen cargada exitosamente.');
        $this->redirect($this->request->getReferrer());
    }

    public function verEliminar() {
        $this->render('perfil/eliminar.twig');
    }

    public function eliminar() {
        $vdt = new Validate\Validator();
        $vdt->addRule('password', new Validate\Rule\MinLength(8))
            ->addRule('password', new Validate\Rule\MaxLength(128));
        $req = $this->request;
        if (!$vdt->validate($req->post())) {
            throw (new TurnbackException())->setErrors($vdt->getErrors());
        }
        if (!$this->session->login($this->session->user('email'), $vdt->getData('password'))) {
            throw (new TurnbackException())->setErrors(array('Contraseña inválida.'));
        }
        $usuario = $this->session->getUser();
        //TODO programar logica
        $this->session->logout();
        $this->flash('success', 'Su cuenta ha sido eliminada.');
        $this->redirectTo('shwIndex');
    }

}