<?php require __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection(array(
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'virtuagora',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => ''
));
$capsule->setAsGlobal();

Capsule::schema()->dropIfExists('moderadores');
Capsule::schema()->dropIfExists('patrullas');
Capsule::schema()->dropIfExists('organismo_integrantes');
Capsule::schema()->dropIfExists('organismos');
Capsule::schema()->dropIfExists('contactos');
Capsule::schema()->dropIfExists('usuario_datos');
Capsule::schema()->dropIfExists('ciudadanos');
Capsule::schema()->dropIfExists('funcionarios');
Capsule::schema()->dropIfExists('usuarios');
