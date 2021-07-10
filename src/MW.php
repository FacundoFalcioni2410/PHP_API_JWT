<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
    use Slim\Psr7\Response as ResponseMW;

    class MW
    {
        public function VerificarDatosUsuario(Request $request, RequestHandler $handler): ResponseMW
        {
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $newResponse = new ResponseMW();

            $params = $request->getParsedBody();
            $json = json_decode($params['usuario_json']);

            if($json != null)
            {
                $retornoJSON->error = "Error al pasar el JSON con los parametros";

                if(!isset($json->correo) && !isset($json->clave))
                {
                    $retornoJSON->error = "No se le paso el correo ni la clave";
                }
                else if(!isset($json->correo))
                {
                    $retornoJSON->error = "No se le paso el correo";
                }
                else if(!isset($json->clave))
                {
                    $retornoJSON->error = "No se le paso la clave";
                }
                else
                {
                    $response = $handler->handle($request);
                    $retornoJSON = json_decode($response->getBody());
                }
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json');
        }
        
        public function VerificarDatosVacios(Request $request, RequestHandler $handler): ResponseMW
        {
            $retornoJSON = new stdClass();
            $retornoJSON->status = 409;
            $newResponse = new ResponseMW();

            $params = $request->getParsedBody();
            $json = json_decode($params["usuario_json"]);

            if($json->correo === "" && $json->clave === "")
            {
                $retornoJSON->error = "El correo y la clave estan vacios";
            }
            else if($json->correo === "")
            {
                $retornoJSON->error = "El correo esta vacio";
            }
            else if($json->clave === "")
            {
                $retornoJSON->error = "La clave esta vacia";
            }
            else
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json');
        }

        public function VerificarCorreoClaveBD(Request $request, RequestHandler $handler): ResponseMW
        {
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $params = $request->getParsedBody();

            $json = $params["usuario_json"];

            $usuario = Usuario::TraerUnoCorreoClave($json);

            if($usuario != null)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());
            }
            else
            {
                $retornoJSON->mensaje = "El correo y clave no existen en la base de datos";
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json'); 
        }

        public function VerificarCorreoBD(Request $request, RequestHandler $handler): ResponseMW
        {
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $params = $request->getParsedBody();

            $json = json_decode($params["usuario_json"]);

            $usuario = Usuario::TraerUnoCorreo($json->correo);

            if($usuario != null)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());
            }
            else
            {
                $retornoJSON->mensaje = "El correo no existe en la base de datos";
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json'); 
        }

        public function VerificarAuto(Request $request, RequestHandler $handler): ResponseMW
        {
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 409;

            $params = $request->getParsedBody();

            $json = json_decode($params["auto_json"]);

            if(($json->precio < 50000 || $json->precio > 600000) && $json->color === "azul")
            {
                $retornoJSON->error = "El precio no esta en el rango permitido y el color azul no esta permitido";
            }
            else if($json->precio < 50000 || $json->precio > 600000)
            {
                $retornoJSON->error = "El precio no esta en el rango permitido";
            }
            else if($json->color === "azul")
            {
                $retornoJSON->error = "El color azul no esta permitido";
            }
            else
            {
                unset($retornoJSON->status);
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json'); 
        }

        public function VerificarToken(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $retorno = Autentificadora::VerificarJWT($jwt);

            if($retorno->verificado)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());
            }
            else
            {
                $retornoJSON->error = $retorno->mensaje;
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json'); 
        }

        public function VerificarPropietario(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 409;
            $retornoJSON->propietario = false;

            $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);
            $obj = $payloadObtenido->payload->data;
            if($obj->perfil === "propietario")
            {
                $retornoJSON->propietario = true;
            }
            else
            {
                $retornoJSON->mensaje = "El usuario es {$obj->perfil}";
            }

            if($retornoJSON->propietario)
            {
                    $response = $handler->handle($request);
                    $retornoJSON = json_decode($response->getBody());
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json'); 
        }

        public function VerificarEncargadoYPropietario(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 409;
            $retornoJSON->perfilPermitido = false;

            $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);
            $obj = $payloadObtenido->payload->data;
            if($obj->perfil === "encargado" || $obj->perfil === "propietario")
            {
                $retornoJSON->perfilPermitido = true;
            }
            else
            {
                $retornoJSON->mensaje = "El usuario es {$obj->perfil}";
            }

            if($retornoJSON->perfilPermitido)
            {
                    $response = $handler->handle($request);
                    $retornoJSON = json_decode($response->getBody());
            }


            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json'); 
        }

        public function RetornarListadoEncargado(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $retorno = Autentificadora::VerificarJWT($jwt);
            $retornoJSON->mensaje = $retorno->mensaje;

            if($retorno->verificado)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());

                $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);
                $obj = $payloadObtenido->payload->data;

                if($obj->perfil === "encargado")
                {
                    foreach($retornoJSON->tabla as $item)
                    {
                        unset($item->id);
                    }
                }
            }
            
            $newResponse->getBody()->write(json_encode($retornoJSON));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }

        public function CantidadColores(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $retorno = Autentificadora::VerificarJWT($jwt);
            $retornoJSON->mensaje = $retorno->mensaje;

            if($retorno->verificado)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());

                $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);
                $obj = $payloadObtenido->payload->data;

                if($obj->perfil === "empleado")
                {
                    $colorArray = array();
                    foreach($retornoJSON->tabla as $item)
                    {
                        array_push($colorArray, $item->color);
                    }

                    $cantidadArray = array_count_values($colorArray);
                    
                    unset($retornoJSON->tabla);
                    unset($retornoJSON->mensaje);
                    $colorArray = array_values(array_unique($colorArray));

                    $retornoJSON->cantidad = "Hay ". count($cantidadArray) . " colores";
                    $retornoJSON->colores = $colorArray;           
                }
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }

        public function MostrarDatosPropietario(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $id = isset($request->getHeader("id")[0]) ? $request->getHeader("id")[0] : null;
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $retorno = Autentificadora::VerificarJWT($jwt);
            $retornoJSON->mensaje = $retorno->mensaje;

            if($retorno->verificado)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());

                $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);
                $obj = $payloadObtenido->payload->data;

                if($obj->perfil === "propietario")
                {
                    if($id != null)
                    {
                        foreach($retornoJSON->tabla as $item)
                        {
                            if($item->id == $id)
                            {
                                unset($retornoJSON->tabla);
                                $retornoJSON->auto = $item;
                                break;
                            }
                        }
                    }
                }
            }
            
            $newResponse->getBody()->write(json_encode($retornoJSON));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }
        
        public function RetornarUsuariosEncargado(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $retorno = Autentificadora::VerificarJWT($jwt);
            $retornoJSON->mensaje = $retorno->mensaje;

            if($retorno->verificado)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());

                $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);
                $obj = $payloadObtenido->payload->data;

                if($obj->perfil === "encargado")
                {
                    foreach($retornoJSON->tabla as $item)
                    {
                        unset($item->id);
                        unset($item->clave);
                    }
                }
            }
            
            $newResponse->getBody()->write(json_encode($retornoJSON));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }

        public function CantidadApellido(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $apellido = isset($request->getHeader("apellido")[0]) ? $request->getHeader("apellido")[0] : null;
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $retorno = Autentificadora::VerificarJWT($jwt);
            $retornoJSON->mensaje = $retorno->mensaje;

            if($retorno->verificado)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());

                $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);
                $obj = $payloadObtenido->payload->data;

                if($obj->perfil === "propietario")
                {
                    $apellidosArray = array();
                    
                    foreach($retornoJSON->tabla as $item)
                    {
                        array_push($apellidosArray, $item->apellido);
                    }

                    $cantidadArray = array_count_values($apellidosArray);
                    
                    unset($retornoJSON->tabla);
                    unset($retornoJSON->mensaje);

                    if($apellido != null)
                    {
                        foreach($cantidadArray as $key => $item)
                        {
                            if($key == $apellido)
                            {
                                $retornoJSON->cantidad = "Hay {$item} apellidos iguales al pasado";
                            }
                        }              
                    }
                    else
                    {
                        $retornoJSON->apellidos = $cantidadArray;
                    }  
                }
            }

            $newResponse->getBody()->write(json_encode($retornoJSON));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }

        public function MostrarUsuariosEmpleado(Request $request, RequestHandler $handler): ResponseMW
        {
            $jwt = $request->getHeader("jwt")[0];
            $newResponse = new ResponseMW();
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;

            $retorno = Autentificadora::VerificarJWT($jwt);
            $retornoJSON->mensaje = $retorno->mensaje;

            if($retorno->verificado)
            {
                $response = $handler->handle($request);
                $retornoJSON = json_decode($response->getBody());

                $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);
                $obj = $payloadObtenido->payload->data;

                if($obj->perfil === "empleado")
                {
                    foreach($retornoJSON->tabla as $item)
                    {
                        unset($item->id);
                        unset($item->correo);
                        unset($item->clave);
                        unset($item->perfil);
                    }
                }
            }
            
            $newResponse->getBody()->write(json_encode($retornoJSON));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }
    }
?>