<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {

    require('src/middlewares/Auth.php');

    $container = $app->getContainer();

    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });

    //Home

    $app->get('/', function($request, $response){
    	$msg = array('msg' => "Hello! This is my home API.");
    	return $this->response->withJson($msg, 200);
    });

    $app->post('/auth', function($request, $response, $args) use ($container){

            $usuario = json_decode($request->getBody());
            if($usuario == null || $usuario->usuario == null || $usuario->senha == null){
                return $response->withJson(array("error"=>array("login"=>"false", "message"=>"Invalid credentials.")), 403);
            }
            $sql = "SELECT idUser, nome, usuario, tipoUser FROM user as u WHERE u.usuario = :usuario AND u.senha = :senha";
            $conn = $container->db;
            $stmt = $conn->prepare($sql);
            $stmt->bindParam("usuario",$usuario->usuario);
            $stmt->bindParam("senha",$usuario->senha);
            $stmt->execute();
            $user = $stmt->fetchObject();
            if($user){
                date_default_timezone_set('America/Sao_Paulo');
                //Se achar o usuário procura tbm pelo tipo do usuário.
                $sql = "SELECT * FROM tipousuario WHERE idTipo = :idTipoUsuario";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam("idTipoUsuario",$user->tipoUser);
                $stmt->execute();
                $tipoUsuario = $stmt->fetchObject();
                $user->tipoUser = $tipoUsuario; //Adciona usuário ao obj de resposta.
                require('src/JWT/JWTWrapper.php');
                $jwt = JWTWrapper::encode([
                    'expiration_sec' => 3600,
                    'userdata' => $user
                ]);

                return $response->withJson(array("login"=>"true","token"=>$jwt));
            }else{
                //Se o usuário, a senha ou ambos estiverem incorretos..
                return $response->withJson(array("error"=>array("login"=>"false", "message"=>"Invalid credentials.")), 403);
            }
    });

    require('routes/spliter.php');

    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
        //Se n houver rota lança 404
        return $res->withJson(array("error"=>array("message"=>"not found")), 404);
    });

};