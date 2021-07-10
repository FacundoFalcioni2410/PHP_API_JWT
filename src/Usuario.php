<?php
    require_once("AccesoDatos.php");
    require_once("Autentificadora.php");
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;
    
    class Usuario
    {
        public $id;
        public $nombre;
        public $apellido;
        public $correo;
        public $clave;
        public $perfil;
        public $foto;


        public static function TraerUsuarios()
        {
            $accesoDatos = AccesoDatos::ObjetoAccesoDatos(); 
            $query = $accesoDatos->RetornarConsulta("SELECT id, correo, clave, nombre, apellido, perfil, foto FROM usuarios");
            
            try
            {
                $query->execute();
                return $query->fetchAll(PDO::FETCH_CLASS, "Usuario");
            }
            catch (PDOException $e)
            {
                echo "Error: {$e->getMessage()}"; 
            }
        }

        public function VerificarUsuario(Request $request, Response $response, array $args): Response
        {
            $jsonRetorno = new stdClass();
            $parametros = $request->getParsedBody();

            $json = $parametros["usuario_json"];

            $usuario = Usuario::TraerUnoCorreoClave($json);

            if($usuario != null)
            {
                $jwt = Autentificadora::CrearJWT($usuario);
                $jsonRetorno->jwt = $jwt;
                $jsonRetorno->status = 200;
                $newResponse = $response->withStatus(200);
                $newResponse->getBody()->write(json_encode($jsonRetorno));

                return $newResponse->withHeader('Content-Type', 'application/json');
            }
            
            $jsonRetorno->token = null;
            $jsonRetorno->status = 403;
            $newResponse = $response->withStatus(403);
            $newResponse->getBody()->write(json_encode($jsonRetorno));

            return $newResponse->withHeader('Content-Type', 'application/json');
        }

        public function ObtenerDataJWT(Request $request, Response $response, array $args): Response
        {
            $retornoJSON = new stdClass();
            $retornoJSON->status = 403;
      
            $token = $request->getHeader("token")[0];

            $retorno = Autentificadora::ObtenerPayLoad($token);
            $retornoJSON->mensaje = $retorno->payload;
            
            if($retorno->exito)
            {
                $retornoJSON->status = 200;
            }
      
            $response->getBody()->write(json_encode($retornoJSON));
      
            return $response->withHeader('Content-Type', 'application/json');
        }

        public static function TraerUnoCorreoClave($json)
        {
            $usuario = json_decode($json);
            $accesoDatos = AccesoDatos::ObjetoAccesoDatos();
            
            $query = $accesoDatos->RetornarConsulta("SELECT * from usuarios WHERE correo = :correo AND clave = :clave");

            $query->bindValue(':clave', $usuario->clave, PDO::PARAM_STR);
            $query->bindValue(':correo', $usuario->correo, PDO::PARAM_STR);

            try
            {
                $query->execute();
                return $query->fetchObject("Usuario");
            }
            catch(PDOException $e)
            {
                echo "Error: {$e->getMessage()}";
            }
        }

        public static function TraerUnoCorreo($correo)
        {
            $accesoDatos = AccesoDatos::ObjetoAccesoDatos();
            
            $query = $accesoDatos->RetornarConsulta("SELECT * from usuarios WHERE correo = :correo");

            $query->bindValue(':correo', $correo, PDO::PARAM_STR);

            try
            {
                $query->execute();
                return $query->fetchObject("Usuario");
            }
            catch(PDOException $e)
            {
                echo "Error: {$e->getMessage()}";
            }
        }

        public function TraerTodos(Request $request, Response $response, array $args): Response 
        {
            $retornoJSON = new stdClass();

            $retornoJSON->exito = true;
            $retornoJSON->mensaje = "Usuarios traidos con exito";
            $retornoJSON->tabla = $usuarios = Usuario::TraerUsuarios();

            $newResponse = $response->withStatus(200, "OK");
            $newResponse->getBody()->write(json_encode($retornoJSON));

            return $newResponse->withHeader('Content-Type', 'application/json');
        }

        public function AgregarUno()
        {
            $accesoDatos = AccesoDatos::ObjetoAccesoDatos();
            
            $query = $accesoDatos->RetornarConsulta("INSERT INTO usuarios (nombre, apellido, correo, clave, perfil, foto) VALUES (:nombre, :apellido, :correo, :clave, :perfil, :foto)");

            $query->bindValue(':nombre',$this->nombre, PDO::PARAM_STR);
            $query->bindValue(':apellido',$this->apellido, PDO::PARAM_STR);
            $query->bindValue(':correo',$this->correo, PDO::PARAM_STR);
            $query->bindValue(':clave',$this->clave, PDO::PARAM_STR);
            $query->bindValue(':perfil',$this->perfil, PDO::PARAM_INT);
            $query->bindValue(':foto',$this->foto, PDO::PARAM_STR);

            $obj = new stdClass();
            $obj->exito = false;
            $obj->mensaje = "No se pudo agregar el usuario";

            try
            {
                $query->execute();
                if($query->rowCount())
                {
                    $obj->exito = true;
                    $obj->mensaje = "Se agrego un usuario con el id {$accesoDatos->RetornarUltimoIdInsertado()}";
                }
            }
            catch(PDOException $e)
            {
                echo "Error: {$e->getMessage()}";
            }

            return json_encode($obj);
        }

        public function AgregarUsuario(Request $request, Response $response, array $args): Response
        {
            $params = $request->getParsedBody();
            $json = json_decode($params["usuario_json"]);
            $foto = $request->getUploadedFiles()["foto"];

            $retornoJSON = new stdClass();
            $retornoJSON->exito = false;
            $retornoJSON->mensaje = "Error al agregar el usuario";
            $retornoJSON->status = 418;
            

            if($json->perfil == "propietario" || $json->perfil == "encargado" || $json->perfil == "empleado")
            {
                $this->correo = $json->correo;
                $this->clave = $json->clave;
                $this->nombre = $json->nombre;
                $this->apellido = $json->apellido;
                $this->perfil = $json->perfil;

                if($foto != NULL)
                {
                    $pathDestino = "../fotos/" . $foto->getClientFilename();
                    $flag = false;
                
                    $tipoDeArchivo = pathinfo($pathDestino,PATHINFO_EXTENSION);
                    $nombreArchivo = $this->nombre . ".". $this->apellido ."." . date("Gis") . "." . $tipoDeArchivo;
                    $pathDestino = "../fotos/" . $nombreArchivo;

                    if($tipoDeArchivo == "jpg" || $tipoDeArchivo == "bmp" || $tipoDeArchivo == "gif" || $tipoDeArchivo == "png" || $tipoDeArchivo == "jpeg")
                    {
                        $foto->moveTo($pathDestino);
                        $this->foto = $pathDestino;
                        $flag = true;
                    }

                    if($flag)
                    {
                        $retorno = json_decode($this->AgregarUno());
                        if($retorno->exito)
                        {
                            $retornoJSON->exito = $retorno->exito;
                            $retornoJSON->mensaje = $retorno->mensaje;
                            $retornoJSON->status = 200;
                            $response->getBody()->write(json_encode($retornoJSON));
                        }else
                        {
                            $retornoJSON->mensaje = $retorno->mensaje;
                            $response->getBody()->write(json_encode($retornoJSON));
                        }
                    }
                }
            }
            else
            {
                $retornoJSON->mensaje = "El perfil pasado es erroneo";
                $response->getBody()->write(json_encode($retornoJSON));
            }
            return $response;
        }
    }
?>