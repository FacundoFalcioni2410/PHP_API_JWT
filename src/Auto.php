<?php

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;

    require_once("AccesoDatos.php");

    class Auto
    {
        public $precio;
        public $modelo;
        public $marca;
        public $color;        

        public function AgregarUno()
        {
            $accesoDatos = AccesoDatos::ObjetoAccesoDatos();
            
            $query = $accesoDatos->RetornarConsulta("INSERT INTO autos (marca, modelo, color, precio) VALUES (:marca, :modelo, :color, :precio)");

            $query->bindValue(':marca',$this->marca, PDO::PARAM_STR);
            $query->bindValue(':modelo',$this->modelo, PDO::PARAM_INT);
            $query->bindValue(':color',$this->color, PDO::PARAM_STR);
            $query->bindValue(':precio',$this->precio, PDO::PARAM_INT);

            $obj = new stdClass();
            $obj->exito = false;
            $obj->mensaje = "No se pudo agregar el auto";

            try
            {
                $query->execute();
                if($query->rowCount())
                {
                    $obj->exito = true;
                    $obj->mensaje = "Se agrego un auto con el id {$accesoDatos->RetornarUltimoIdInsertado()}";
                }
            }
            catch(PDOException $e)
            {
                echo "Error: {$e->getMessage()}";
            }

            return json_encode($obj);
        }

        public function AltaAuto(Request $request, Response $response, array $args): Response
        {
            $params = $request->getParsedBody();
            
            $json = json_decode($params["auto_json"]);

            $auto = new Auto();

            $retornoJSON = new stdClass();
            $retornoJSON->exito = false;
            $retornoJSON->mensaje = "Error al agregar el auto";
            $retornoJSON->status = 418;

            $auto->marca = $json->marca;
            $auto->modelo = $json->modelo;
            $auto->color = $json->color;
            $auto->precio = $json->precio;

            $retorno = json_decode($auto->AgregarUno());

            if($retorno->exito)
            {
                $retornoJSON->exito = $retorno->exito;
                $retornoJSON->mensaje = $retorno->mensaje;
                $retornoJSON->status = 200;
                $response->getBody()->write(json_encode($retornoJSON));
            }
            else
            {
                $retornoJSON->mensaje = $retorno->mensaje;
                $response->getBody()->write(json_encode($retornoJSON));
            }

            return $response;
        }

        public static function TraerAutos()
        {
            $accesoDatos = AccesoDatos::ObjetoAccesoDatos(); 
            $query = $accesoDatos->RetornarConsulta("SELECT id, marca, modelo, precio, color FROM autos");
            
            try
            {
                $query->execute();
                return $query->fetchAll(PDO::FETCH_CLASS, "Auto");
            }
            catch (PDOException $e)
            {
                echo "Error: {$e->getMessage()}"; 
            }
        }

        public function TraerTodos(Request $request, Response $response, array $args): Response 
        {
            $autos = Auto::TraerAutos();

            $retornoJSON = new stdClass();

            $retornoJSON->exito = false;
            $retornoJSON->mensaje = "Error al traer los autos";
            $retornoJSON->tabla = null;
            $retornoJSON->status = 424;

            if(count($autos))
            {
                $retornoJSON->exito = true;
                $retornoJSON->mensaje = "Autos traidos con exito";
                $retornoJSON->tabla = $autos;
                $retornoJSON->status = 200;
            }

            $response->getBody()->write(json_encode($retornoJSON));

            return $response->withHeader('Content-Type', 'application/json');
        }

        public static function EliminarUno($id)
        {
            $accesoDatos = AccesoDatos::ObjetoAccesoDatos();
            
            $query = $accesoDatos->RetornarConsulta("DELETE FROM autos WHERE id = :id");

            $query->bindValue(':id',$id, PDO::PARAM_INT);

            $obj = new stdClass();
            $obj->exito = false;
            $obj->mensaje = "No se pudo eliminar el auto";

            try
            {
                $query->execute();
                if($query->rowCount())
                {
                    $obj->exito = true;
                    $obj->mensaje = "Se elimino el auto con exito";
                }
            }
            catch(PDOException $e)
            {
                echo "Error: {$e->getMessage()}";
            }

            return json_encode($obj);
        }

        public function EliminarAuto(Request $request, Response $response, array $args): Response
        {
            $jwt = $request->getHeader("jwt")[0];
            $id = $request->getHeader("id")[0];

            $retornoJSON = new stdClass();
            $retornoJSON->exito = false;
            $retornoJSON->status = 418;

            $retorno = Autentificadora::VerificarJWT($jwt);

            if($retorno->verificado)
            {
                $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);

                $obj = $payloadObtenido->payload->data;

                if($obj->perfil == "propietario")
                {
                    $retorno = json_decode(Auto::EliminarUno($id));
                    if($retorno->exito === true)
                    {
                        $retornoJSON->exito = true;
                        $retornoJSON->status = 200;
                        $retornoJSON->mensaje = $retorno->mensaje;
                        $retornoJSON->usuario = $obj;
                    }
                    else
                    {
                        $retornoJSON->mensaje = $retorno->mensaje;
                    }
                }
                else
                {
                    $retornoJSON->mensaje = "No tiene los permisos para borrar un auto, debe ser propietario";
                }
            }
            $response->getBody()->write(json_encode($retornoJSON));

            return $response->withHeader('Content-Type', 'application/json');
        }

        public function ModificarAuto(Request $request, Response $response, array $args): Response
        {
            $jwt = $request->getHeader("jwt")[0];
            $auxAuto = json_decode($request->getHeader("auto_json")[0]);

            $auto = new Auto();
            $auto->id = $auxAuto->id;
            $auto->marca = $auxAuto->marca;
            $auto->precio = $auxAuto->precio;
            $auto->color = $auxAuto->color;
            $auto->modelo = $auxAuto->modelo;
            
            $retornoJSON = new stdClass();
            $retornoJSON->exito = false;
            $retornoJSON->status = 418;

            $retorno = Autentificadora::VerificarJWT($jwt);

            if($retorno->verificado)
            {
                $payloadObtenido = Autentificadora::ObtenerPayLoad($jwt);

                $obj = $payloadObtenido->payload->data;

                if($obj->perfil == "propietario" || $obj->perfil == "encargado")
                {
                    $retorno = json_decode($auto->ModificarUno());
                    if($retorno->exito === true)
                    {
                        $retornoJSON->exito = true;
                        $retornoJSON->status = 200;
                        $retornoJSON->mensaje = $retorno->mensaje;
                        $retornoJSON->usuario = $obj;
                    }
                    else
                    {
                        $retornoJSON->mensaje = $retorno->mensaje;
                    }
                }
                else
                {
                    $retornoJSON->mensaje = "No tiene los permisos para modificar un auto, debe ser propietario o encargado";
                }
            }
            $response->getBody()->write(json_encode($retornoJSON));

            return $response->withHeader('Content-Type', 'application/json');
        }

        public function ModificarUno()
        {
            $accesoDatos = AccesoDatos::ObjetoAccesoDatos();
            
            $query = $accesoDatos->RetornarConsulta("UPDATE autos SET color = :color, marca = :marca, precio = :precio, modelo = :modelo WHERE id = :id");

            $query->bindValue(':color',$this->color, PDO::PARAM_STR);
            $query->bindValue(':marca',$this->marca, PDO::PARAM_STR);
            $query->bindValue(':precio',$this->precio, PDO::PARAM_INT);
            $query->bindValue(':modelo',$this->modelo, PDO::PARAM_INT);
            $query->bindValue(':id',$this->id, PDO::PARAM_INT);

            $obj = new stdClass();
            $obj->exito = false;
            $obj->mensaje = "No se pudo modificar el auto";

            try
            {
                $query->execute();
                if($query->rowCount())
                {
                    $obj->exito = true;
                    $obj->mensaje = "Se modifico el auto con exito";
                }
            }
            catch(PDOException $e)
            {
                echo "Error: {$e->getMessage()}";
            }

            return json_encode($obj);
        }
    }
?>