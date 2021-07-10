<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Psr7\Response as ResponseMW;
    use Slim\Factory\AppFactory;
    use \Slim\Routing\RouteCollectorProxy;

    require __DIR__ . '../../vendor/autoload.php';
    require_once("../src/Usuario.php");
    require_once("../src/Auto.php");
    require_once("../src/MW.php");

    $app = AppFactory::create();
    
    $app->post('/usuarios', \Usuario::class . ':AgregarUsuario')->add(\MW::class . ":VerificarCorreoBD")->add(\MW::class . ":VerificarDatosVacios")->add(\MW::class . ":VerificarDatosUsuario");
    $app->get('/', \Usuario::class . ':TraerTodos')->add(\MW::class . ":RetornarUsuariosEncargado")->add(\MW::class . ":CantidadApellido")->add(\MW::class . ":MostrarUsuariosEmpleado");
    $app->post('/', \Auto::class . ":AltaAuto")->add(\MW::class . ':VerificarAuto');
    $app->get('/autos', \Auto::class . ":TraerTodos")->add(\MW::class . ':RetornarListadoEncargado')->add(\MW::class . ':CantidadColores')->add(\MW::class . ":MostrarDatosPropietario");
    $app->post('/login', \Usuario::class . ":VerificarUsuario")->add(\MW::class . ":VerificarCorreoClaveBD")->add(\MW::class . ":VerificarDatosVacios")->add(\MW::class . ":VerificarDatosUsuario");;
    $app->get('/login', \Usuario::class . ":ObtenerDataJWT");
    $app->delete('/', \Auto::class . ":EliminarAuto")->add(\MW::class . ":VerificarPropietario")->add(\MW::class . ":VerificarToken");
    $app->put('/', \Auto::class . ":ModificarAuto")->add(\MW::class . ":VerificarEncargadoYPropietario")->add(\MW::class . ":VerificarToken");
    
    // $app->post('/login[/]', \Verificadora::class . ':VerificarUsuario')->add(\Verificadora::class . ':ValidarParametrosUsuario');
    // $app->get('/login/test', \Verificadora::class . ':ObtenerDataJWT')->add(\Verificadora::class . ':ChequearJWT');

    // $app->group('/json_bd', function(RouteCollectorProxy $grupo)
    // {
    //     $grupo->get('/', \cd::class . ':TraerTodos');
    //     $grupo->get('/{id}', \cd::class . ':TraerUno');
    //     $grupo->post('/', \cd::class . ':Agregar')->add(\Verificadora::class . ':ValidarParametrosCDAgregar');
    //     $grupo->put('/', \cd::class . ':Modificar')->add(\Verificadora::class . ':ValidarParametrosCDModificar');
    //     $grupo->delete('/', \cd::class . ':Eliminar')->add(\Verificadora::class . ':ValidarParametrosCDEliminar');
    // })->add(\Verificadora::class . ':ChequearJWT');

    $app->run();
?>